<?php

namespace LegacyFighter\Cabs\Service;

//ss If this cla will still be here in 2022 I will quit.
use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\DTO\AddressDTO;
use LegacyFighter\Cabs\DTO\DriverPositionDTOV2;
use LegacyFighter\Cabs\DTO\TransitDTO;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverSession;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Repository\AddressRepository;
use LegacyFighter\Cabs\Repository\ClientRepository;
use LegacyFighter\Cabs\Repository\DriverPositionRepository;
use LegacyFighter\Cabs\Repository\DriverRepository;
use LegacyFighter\Cabs\Repository\DriverSessionRepository;
use LegacyFighter\Cabs\Repository\TransitRepository;

class TransitService
{
    private DriverRepository $driverRepository;
    private TransitRepository $transitRepository;
    private ClientRepository $clientRepository;
    private InvoiceGenerator $invoiceGenerator;
    private DriverNotificationService $notificationService;
    private DistanceCalculator $distanceCalculator;
    private DriverPositionRepository $driverPositionRepository;
    private DriverSessionRepository $driverSessionRepository;
    private CarTypeService $carTypeService;
    private GeocodingService $geocodingService;
    private AddressRepository $addressRepository;
    private DriverFeeService $driverFeeService;
    private Clock $clock;
    private AwardsService $awardsService;

    public function __construct(
        DriverRepository $driverRepository,
        TransitRepository $transitRepository,
        ClientRepository $clientRepository,
        InvoiceGenerator $invoiceGenerator,
        DriverNotificationService $notificationService,
        DistanceCalculator $distanceCalculator,
        DriverPositionRepository $driverPositionRepository,
        DriverSessionRepository $driverSessionRepository,
        CarTypeService $carTypeService,
        GeocodingService $geocodingService,
        AddressRepository $addressRepository,
        DriverFeeService $driverFeeService,
        Clock $clock,
        AwardsService $awardsService
    )
    {
        $this->driverRepository = $driverRepository;
        $this->transitRepository = $transitRepository;
        $this->clientRepository = $clientRepository;
        $this->invoiceGenerator = $invoiceGenerator;
        $this->notificationService = $notificationService;
        $this->distanceCalculator = $distanceCalculator;
        $this->driverPositionRepository = $driverPositionRepository;
        $this->driverSessionRepository = $driverSessionRepository;
        $this->carTypeService = $carTypeService;
        $this->geocodingService = $geocodingService;
        $this->addressRepository = $addressRepository;
        $this->driverFeeService = $driverFeeService;
        $this->clock = $clock;
        $this->awardsService = $awardsService;
    }

    public function createTransit(TransitDTO $transitDTO): Transit
    {
        $from = $this->addressFromDto($transitDTO->getFrom());
        $to = $this->addressFromDto($transitDTO->getTo());
        return $this->createTransitFrom($transitDTO->getClientDTO()->getId(), $from, $to, $transitDTO->getCarClass());
    }

    public function createTransitFrom(int $clientId, Address $from, Address $to, string $carClass): Transit
    {
        $client = $this->clientRepository->getOne($clientId);

        if($client === null) {
            throw new \InvalidArgumentException('Client does not exist, id = '.$clientId);
        }

        $transit = new Transit();

        // FIXME later: add some exceptions handling
        $geoFrom = $this->geocodingService->geocodeAddress($from);
        $geoTo = $this->geocodingService->geocodeAddress($to);

        $transit->setClient($client);
        $transit->setFrom($from);
        $transit->setTo($to);
        $transit->setCarType($carClass);
        $transit->setStatus(Transit::STATUS_DRAFT);
        $transit->setDateTime($this->clock->now());
        $transit->setKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));

        return $this->transitRepository->save($transit);
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

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        // FIXME later: add some exceptions handling
        $geoFromNew = $this->geocodingService->geocodeAddress($newAddress);
        $geoFromOld = $this->geocodingService->geocodeAddress($transit->getFrom());

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

        if(!($transit->getStatus() === Transit::STATUS_DRAFT) ||
            ($transit->getStatus() === Transit::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT) ||
            ($transit->getPickupAddressChangeCounter() > 2) ||
            ($distanceInKMeters > 0.25)
        ) {
            throw new \InvalidArgumentException('Address \'from\' cannot be changed, id = '.$transitId);
        }

        $transit->setFrom($newAddress);
        $transit->setKm($this->distanceCalculator->calculateByMap($geoFromNew[0], $geoFromNew[1], $geoFromOld[0], $geoFromOld[1]));
        $transit->setPickupAddressChangeCounter($transit->getPickupAddressChangeCounter() + 1);
        $this->transitRepository->save($transit);

        foreach ($transit->getProposedDrivers() as $driver) {
            $this->notificationService->notifyAboutChangedTransitAddress($driver->getId(), $transit->getId());
        }
    }

    public function changeTransitAddressToNew(int $transitId, Address $newAddress): void
    {
        $newAddress = $this->addressRepository->save($newAddress);
        $transit = $this->transitRepository->getOne($transitId);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        if($transit->getStatus() === Transit::STATUS_COMPLETED) {
            throw new \InvalidArgumentException('Address \'to\' cannot be changed, id = '.$transitId);
        }

        // FIXME later: add some exceptions handling
        $geoFrom = $this->geocodingService->geocodeAddress($transit->getFrom());
        $geoTo = $this->geocodingService->geocodeAddress($newAddress);

        $transit->setTo($newAddress);
        $transit->setKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));

        if($transit->getDriver() !== null) {
            $this->notificationService->notifyAboutChangedTransitAddress($transit->getDriver()->getId(), $transit->getId());
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
        if(!in_array($transit->getStatus(),  [Transit::STATUS_DRAFT, Transit::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT, Transit::STATUS_TRANSIT_TO_PASSENGER], true)) {
            throw new \InvalidArgumentException('Transit cannot be cancelled, id = '.$transitId);
        }

        if($transit->getDriver() !== null) {
            $this->notificationService->notifyAboutCancelledTransit($transit->getDriver()->getId(), $transitId);
        }

        $transit->setStatus(Transit::STATUS_CANCELLED);
        $transit->setDriver(null);
        $transit->setKm(0.0);
        $transit->setAwaitingDriversResponses(0);
        $this->transitRepository->save($transit);
    }

    public function publishTransit(int $transitId): Transit
    {
        $transit = $this->transitRepository->getOne($transitId);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        $transit->setStatus(Transit::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT);
        $transit->setPublished($this->clock->now());
        $this->transitRepository->save($transit);

        return $this->findDriversForTransit($transitId);
    }

    // Abandon hope all ye who enter here...
    public function findDriversForTransit(int $transitId): Transit
    {
        $transit = $this->transitRepository->getOne($transitId);

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
                    if(($transit->getPublished()->modify('+300 seconds') > $this->clock->now())
                        ||
                        ($distanceToCheck >= 20)
                        ||
                        // Should it be here? How is it even possible due to previous status check above loop?
                        ($transit->getStatus() === Transit::STATUS_CANCELLED)
                    ) {
                        $transit->setStatus(Transit::STATUS_DRIVER_ASSIGNMENT_FAILED);
                        $transit->setDriver(null); $transit->setKm(0.0);
                        $transit->setAwaitingDriversResponses(0);
                        $this->transitRepository->save($transit);
                        return $transit;
                    }
                    $geocoded = [];


                    try {
                        $geocoded = $this->geocodingService->geocodeAddress($transit->getFrom());
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

                    $driversAvgPositions = $this->driverPositionRepository
                        ->findAverageDriverPositionSince($latitudeMin, $latitudeMax, $longitudeMin, $longitudeMax, $this->clock->now()->modify('-5 minutes'));

                    if(count($driversAvgPositions) !== 0) {
                        usort(
                            $driversAvgPositions,
                            fn(DriverPositionDTOV2 $d1, DriverPositionDTOV2 $d2) =>
                                sqrt(pow($latitude - $d1->getLatitude(), 2) + pow($longitude - $d1->getLongitude(), 2)) <=>
                                sqrt(pow($latitude - $d2->getLatitude(), 2) + pow($longitude - $d2->getLongitude(), 2))
                        );
                        $driversAvgPositions = array_slice($driversAvgPositions, 0, 20);

                        $carClasses = [];
                        $activeCarClasses = $this->carTypeService->findActiveCarClasses();
                        if(count($activeCarClasses) === 0) {
                            return $transit;
                        }
                        if($transit->getCarType()

                            !== null) {
                            if(in_array($transit->getCarType(), $activeCarClasses)) {
                                $carClasses[] = $transit->getCarType();
                            }else {
                                return $transit;
                                }
                        } else {
                            $carClasses = $activeCarClasses;
                        }

                        $drivers = array_map(fn(DriverPositionDTOV2 $dp) => $dp->getDriver(), $driversAvgPositions);

                        $activeDriverIdsInSpecificCar = array_map(
                            fn(DriverSession $ds)
                                => $ds->getDriver()->getId(),

                            $this->driverSessionRepository->findAllByLoggedOutAtNullAndDriverInAndCarClassIn($drivers, $carClasses));

                        $driversAvgPositions = array_filter(
                            $driversAvgPositions,
                            fn(DriverPositionDTOV2 $dp) => in_array($dp->getDriver()->getId(), $activeDriverIdsInSpecificCar)
                        );

                        // Iterate across average driver positions
                        foreach ($driversAvgPositions as $driverAvgPosition) {
                            /** @var DriverPositionDTOV2 $driverAvgPosition */
                            $driver = $driverAvgPosition->getDriver();
                            if($driver->getStatus() === Driver::STATUS_ACTIVE &&

                                    $driver->getOccupied() == false) {
                                if(!in_array($driver,
                                        $transit->getDriversRejections())) {
                                    $proposedDrivers = $transit->getProposedDrivers();
                                    $proposedDrivers[] = $transit;
                                    $transit->setProposedDrivers($proposedDrivers); $transit->setAwaitingDriversResponses($transit->getAwaitingDriversResponses() + 1);
                                    $this->notificationService->notifyAboutPossibleTransit($driver->getId(), $transitId);
                                }
                            } else {
                                // Not implemented yet!
                            }
                        }

                        $this->transitRepository->save($transit);

                    } else {
                        // Next iteration, no drivers at specified area
                        continue;
                    }
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
        } else {
            $transit = $this->transitRepository->getOne($transitId);

            if($transit === null) {
                throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
            } else {
                if($transit->getDriver() !== null) {
                    throw new \RuntimeException('Transit already accepted, id = '.$transitId);
                } else {
                    if(in_array($driver, $transit->getDriversRejections())) {
                        throw new \RuntimeException('"Driver out of possible drivers, id = '.$driverId);
                    } else {
                        $transit->setDriver($driver);
                        $transit->setAwaitingDriversResponses(0);
                        $transit->setAcceptedAt($this->clock->now());
                        $transit->setStatus(Transit::STATUS_TRANSIT_TO_PASSENGER);
                        $this->transitRepository->save($transit);
                        $driver->setOccupied(true);
                        $this->driverRepository->save($driver);
                    }
                }
            }
        }
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

        if($transit->getStatus() !== Transit::STATUS_TRANSIT_TO_PASSENGER) {
            throw new \InvalidArgumentException('Transit cannot be started, id = '.$transitId);
        }

        $transit->setStatus(Transit::STATUS_IN_TRANSIT);
        $transit->setStarted(new \DateTimeImmutable());
        $this->transitRepository->save($transit);
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

        $rejectedDrivers = $transit->getDriversRejections();
        $rejectedDrivers[] = $driver;
        $transit->setDriversRejections($rejectedDrivers);
        $transit->setAwaitingDriversResponses($transit->getAwaitingDriversResponses() - 1);
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

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$transitId);
        }

        if($transit->getStatus() === Transit::STATUS_IN_TRANSIT) {
            // FIXME later: add some exceptions handling
            $geoFrom = $this->geocodingService->geocodeAddress($transit->getFrom());
            $geoTo = $this->geocodingService->geocodeAddress($transit->getTo());

            $transit->setTo($destinationAddress);
            $transit->setKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));
            $transit->setStatus(Transit::STATUS_COMPLETED);
            $transit->calculateFinalCosts();
            $driver->setOccupied(false);
            $transit->setCompleteAt($this->clock->now());
            $driverFee = $this->driverFeeService->calculateDriverFee($transitId);
            $transit->setDriversFee($driverFee);
            $this->driverRepository->save($driver);
            $this->awardsService->registerMiles($transit->getClient()->getId(), $transitId);
            $this->transitRepository->save($transit);
            $this->invoiceGenerator->generate($transit->getPrice()->toInt(), $transit->getClient()->getName() . ' ' . $transit->getClient()->getLastName());
        } else {
            throw new \RuntimeException('Cannot complete Transit, id = '.$transitId);
        }
    }

    public function loadTransit(int $id): TransitDTO
    {
        return TransitDTO::from($this->transitRepository->getOne($id));
    }
}
