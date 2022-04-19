<?php

namespace LegacyFighter\Cabs\Service;

// If this class will still be here in 2022 I will quit.
use LegacyFighter\Cabs\CarFleet\CarTypeService;
use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Crm\ClientRepository;
use LegacyFighter\Cabs\DriverFleet\DriverFeeService;
use LegacyFighter\Cabs\DriverFleet\DriverRepository;
use LegacyFighter\Cabs\DriverFleet\DriverService;
use LegacyFighter\Cabs\DTO\TransitDTO;
use LegacyFighter\Cabs\Entity\Events\TransitCompleted;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Address\AddressRepository;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Geolocation\DistanceCalculator;
use LegacyFighter\Cabs\Geolocation\GeocodingService;
use LegacyFighter\Cabs\Invocing\InvoiceGenerator;
use LegacyFighter\Cabs\Loyalty\AwardsService;
use LegacyFighter\Cabs\Notification\DriverNotificationService;
use LegacyFighter\Cabs\Repository\TransitRepository;
use LegacyFighter\Cabs\Tracking\DriverTrackingService;
use LegacyFighter\Cabs\TransitDetails\TransitDetailsFacade;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TransitService
{
    public function __construct(
        private DriverRepository $driverRepository,
        private TransitRepository $transitRepository,
        private ClientRepository $clientRepository,
        private InvoiceGenerator $invoiceGenerator,
        private DriverNotificationService $notificationService,
        private DistanceCalculator $distanceCalculator,
        private DriverTrackingService $driverTrackingService,
        private DriverService $driverService,
        private CarTypeService $carTypeService,
        private GeocodingService $geocodingService,
        private AddressRepository $addressRepository,
        private DriverFeeService $driverFeeService,
        private TransitDetailsFacade $transitDetailsFacade,
        private Clock $clock,
        private AwardsService $awardsService,
        private EventDispatcherInterface $dispatcher
    )
    { }

    public function createTransit(TransitDTO $transitDTO): TransitDTO
    {
        $from = $this->addressFromDto($transitDTO->getFrom());
        $to = $this->addressFromDto($transitDTO->getTo());
        return $this->loadTransit($this->createTransitFrom($transitDTO->getClientDTO()->getId(), $from, $to, $transitDTO->getCarClass())->getId());
    }

    public function createTransitFrom(int $clientId, Address $from, Address $to, ?string $carClass): Transit
    {
        $client = $this->clientRepository->getOne($clientId);

        if($client === null) {
            throw new \InvalidArgumentException('Client does not exist, id = '.$clientId);
        }

        // FIXME later: add some exceptions handling
        $geoFrom = $this->geocodingService->geocodeAddress($from);
        $geoTo = $this->geocodingService->geocodeAddress($to);

        $now = $this->clock->now();
        $distance = Distance::ofKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));
        $transit = new Transit($client, $now, $distance);
        $estimatedPrice = $transit->estimateCost();
        $transit = $this->transitRepository->save($transit);
        $this->transitDetailsFacade->transitRequested($now, $transit->getId(), $from, $to, $distance, $client, $carClass, $estimatedPrice, $transit->getTariff());
        return $transit;
    }

    private function addressFromDto(AddressDTO $addressDTO): Address
    {
        $address = $addressDTO->toAddressEntity();
        return $this->addressRepository->save($address);
    }

    public function changeTransitAddressFromNew(int $transitId, Address $newAddress): void
    {
        $newAddress = $this->addressRepository->save($newAddress);
        $transit = $this->transitRepository->getOne($transitId);
        $transitDetails = $this->transitDetailsFacade->find($transitId);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        // FIXME later: add some exceptions handling
        $geoFromNew = $this->geocodingService->geocodeAddress($newAddress);
        $geoFromOld = $this->geocodingService->geocodeAddress($transitDetails->from->toAddressEntity());

        // https://www.geeksforgeeks.org/program-distance-two-points-earth/
        // The math module contains a function
        // named toRadians which converts from
        // degrees to radians.
        $lon1 = deg2rad($geoFromNew[1]);
        $lon2 = deg2rad($geoFromOld[1]);
        $lat1 = deg2rad($geoFromNew[0]);
        $lat2 = deg2rad($geoFromOld[0]);

        // Haversine formula
        $dlon = $lon2 - $lon1;
        $dlat = $lat2 - $lat1;
        $a = pow(sin($dlat / 2), 2)
            + cos($lat1) * cos($lat2)
            *pow(sin($dlon/2),2);

        $c = 2 * asin(sqrt($a));

        // Radius of earth in kilometers. Use 3956 for miles
        $r = 6371;

        // calculate the result
        $distanceInKMeters = $c * $r;

        $distance = Distance::ofKm($this->distanceCalculator->calculateByMap($geoFromNew[0], $geoFromNew[1], $geoFromOld[0], $geoFromOld[1]));
        $transit->changePickupTo($newAddress, $distance, $distanceInKMeters);
        $this->transitRepository->save($transit);
        $this->transitDetailsFacade->pickupChangedTo($transitId, $newAddress, $distance);

        foreach ($transit->getProposedDrivers() as $driverId) {
            $this->notificationService->notifyAboutChangedTransitAddress($driverId, $transit->getId());
        }
    }

    public function changeTransitAddressToNew(int $transitId, Address $newAddress): void
    {
        $newAddress = $this->addressRepository->save($newAddress);
        $transit = $this->transitRepository->getOne($transitId);
        $transitDetails = $this->transitDetailsFacade->find($transitId);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        // FIXME later: add some exceptions handling
        $geoFrom = $this->geocodingService->geocodeAddress($transitDetails->from->toAddressEntity());
        $geoTo = $this->geocodingService->geocodeAddress($newAddress);

        $distance = Distance::ofKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));
        $transit->changeDestinationTo($newAddress, $distance);
        $this->transitDetailsFacade->destinationChanged($transitId, $newAddress, $distance);
        if($transit->getDriverId() !== null) {
            $this->notificationService->notifyAboutChangedTransitAddress($transit->getDriverId(), $transit->getId());
        }
    }

    public function changeTransitAddressTo(int $transitId, AddressDTO $newAddress): void
    {
        $this->changeTransitAddressToNew($transitId, $this->addressFromDto($newAddress));
    }

    public function changeTransitAddressFrom(int $transitId, AddressDTO $newAddress): void
    {
        $this->changeTransitAddressFromNew($transitId, $this->addressFromDto($newAddress));
    }

    public function cancelTransit(int $transitId): void
    {
        $transit = $this->transitRepository->getOne($transitId);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        if($transit->getDriverId() !== null) {
            $this->notificationService->notifyAboutCancelledTransit($transit->getDriverId(), $transitId);
        }

        $transit->cancel();
        $this->transitDetailsFacade->transitCancelled($transitId);
        $this->transitRepository->save($transit);
    }

    public function publishTransit(int $transitId): Transit
    {
        $transit = $this->transitRepository->getOne($transitId);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        $now = $this->clock->now();
        $transit->publishAt($now);
        $this->transitRepository->save($transit);
        $this->transitDetailsFacade->transitPublished($transitId, $now);

        return $this->findDriversForTransit($transitId);
    }

    // Abandon hope all ye who enter here...
    public function findDriversForTransit(int $transitId): Transit
    {
        $transit = $this->transitRepository->getOne($transitId);
        $transitDetail = $this->transitDetailsFacade->find($transitId);

        if($transit !== null) {
            if($transit->getStatus() === Transit::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT) {


                $distanceToCheck = 0;

                // Tested on production, works as expected.
                // If you change this code and the system will collapse AGAIN, I'll find you...
                while (true) {
                    if($transit->getAwaitingDriversResponses()
                        > 4) {
                        return $transit;
                    }

                    $distanceToCheck++;

                    // FIXME: to refactor when the final business logic will be determined
                    if($transit->shouldNotWaitForDriverAnyMore($this->clock->now()) || $distanceToCheck >= 20) {
                        $transit->failDriverAssignment();
                        $this->transitRepository->save($transit);
                        return $transit;
                    }
                    $geocoded = [];


                    try {
                        $geocoded = $this->geocodingService->geocodeAddress($transitDetail->from->toAddressEntity());
                    } catch (\Throwable $throwable) {
                        // Geocoding failed! Ask Jessica or Bryan for some help if needed.
                    }

                    $longitude = $geocoded[1];
                    $latitude = $geocoded[0];

                    //https://gis.stackexchange.com/questions/2951/algorithm-for-offsetting-a-latitude-longitude-by-some-amount-of-meters
                    //Earth’s radius, sphere
                    //double R = 6378;
                    $R = 6371; // Changed to 6371 due to Copy&Paste pattern from different source

                    //offsets in meters
                    $dn = $distanceToCheck;
                    $de = $distanceToCheck;

                    //Coordinate offsets in radians
                    $dLat = $dn / $R;
                    $dLon = $de / ($R * cos(M_PI * $latitude / 180));

                    //Offset positions, decimal degrees
                    $latitudeMin = $latitude - $dLat * 180 / M_PI;
                    $latitudeMax = $latitude + $dLat *
                        180 / M_PI;
                    $longitudeMin = $longitude - $dLon *
                        180 / M_PI;
                    $longitudeMax = $longitude + $dLon * 180 / M_PI;

                    $carClasses = $this->choosePossibleCarClasses($transitDetail->carType);
                    if($carClasses === []) {
                        return $transit;
                    }

                    $driversAvgPositions = $this->driverTrackingService->findActiveDriversNearby($latitudeMin, $latitudeMax, $longitudeMin, $longitudeMax, $latitude, $longitude, $carClasses);
                    if($driversAvgPositions === []) {
                        //next iteration
                        continue;
                    }

                    // Iterate across average driver positions
                    foreach ($driversAvgPositions as $driverAvgPosition) {
                        if($transit->canProposeTo($driverAvgPosition->getDriverId())) {
                            $transit->proposeTo($driverAvgPosition->getDriverId());
                            $this->notificationService->notifyAboutPossibleTransit($driverAvgPosition->getDriverId(), $transit->getId());
                        }
                    }

                    return $transit;
                }
            } else {
                throw new \InvalidArgumentException('..., id = '.$transitId);
            }
        } else {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }
    }

    public function acceptTransit(int $driverId, int $transitId): void
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        }
        $transit = $this->transitRepository->getOne($transitId);
        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        $now = $this->clock->now();
        $transit->acceptBy($driver->getId(), $now);
        $this->transitRepository->save($transit);
        $this->driverRepository->save($driver);
        $this->transitDetailsFacade->transitAccepted($transitId, $now, $driverId);
    }

    public function startTransit(int $driverId, int $transitId): void
    {
        $driver = $this->driverRepository->getOne($driverId);

        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        }

        $transit = $this->transitRepository->getOne($transitId);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        $now = $this->clock->now();
        $transit->start($now);
        $this->transitRepository->save($transit);
        $this->transitDetailsFacade->transitStarted($transitId, $now);
    }

    public function rejectTransit(int $driverId, int $transitId): void
    {
        $driver = $this->driverRepository->getOne($driverId);

        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        }

        $transit = $this->transitRepository->getOne($transitId);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        $transit->rejectBy($driver->getId());
        $this->transitRepository->save($transit);
    }

    public function completeTransit(int $driverId, int $transitId, AddressDTO $destinationAddress): void
    {
        $this->completeTransitFrom($driverId, $transitId, $destinationAddress->toAddressEntity());
    }

    public function completeTransitFrom(int $driverId, int $transitId, Address $destinationAddress): void
    {
        $destinationAddress = $this->addressRepository->save($destinationAddress);
        $driver = $this->driverRepository->getOne($driverId);

        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        }

        $transit = $this->transitRepository->getOne($transitId);
        $transitDetails = $this->transitDetailsFacade->find($transitId);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        // FIXME later: add some exceptions handling
        $geoFrom = $this->geocodingService->geocodeAddress($transitDetails->from->toAddressEntity());
        $geoTo = $this->geocodingService->geocodeAddress($transitDetails->to->toAddressEntity());
        $distance = Distance::ofKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));
        $now = $this->clock->now();
        $transit->completeAt($now, $destinationAddress, $distance);
        $driverFee = $this->driverFeeService->calculateDriverFee($transit->getPrice(), $driverId);
        $driver->setOccupied(false);
        $this->driverRepository->save($driver);
        $this->awardsService->registerMiles($transitDetails->client->getId(), $transitId);
        $this->transitRepository->save($transit);
        $this->transitDetailsFacade->transitCompleted($transitId, $now, $transit->getPrice(), $driverFee);
        $this->invoiceGenerator->generate($transit->getPrice()->toInt(), $transitDetails->client->getName() . ' ' . $transitDetails->client->getLastName());
        $this->dispatcher->dispatch(new TransitCompleted(
            $transitDetails->client->getId(),
            $transitId,
            $transitDetails->from->getHash(),
            $transitDetails->to->getHash(),
            $transitDetails->started,
            $now
        ));

    }

    public function loadTransit(int $id): TransitDTO
    {
        $transit = $this->transitRepository->getOne($id);

        return TransitDTO::from(
            $this->transitDetailsFacade->find($id),
            $this->driverService->loadDrivers($transit->getProposedDrivers()),
            $transit->getDriverId()
        );
    }

    /**
     * @return string[]
     */
    private function choosePossibleCarClasses(?string $carClass = null): array
    {
        $carClasses = [];
        $activeCarClasses = $this->carTypeService->findActiveCarClasses();
        if($carClass !== null) {
            if(in_array($carClass, $activeCarClasses, true)) {
                $carClasses[] = $carClass;
            }
        } else {
            $carClasses = $activeCarClasses;
        }

        return $carClasses;
    }
}
