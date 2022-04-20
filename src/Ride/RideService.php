<?php

namespace LegacyFighter\Cabs\Ride;

// If this class will still be here in 2022 I will quit.
use LegacyFighter\Cabs\Assignment\DriverAssignmentFacade;
use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Crm\ClientRepository;
use LegacyFighter\Cabs\DriverFleet\DriverFeeService;
use LegacyFighter\Cabs\DriverFleet\DriverRepository;
use LegacyFighter\Cabs\DriverFleet\DriverService;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Address\AddressRepository;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Geolocation\DistanceCalculator;
use LegacyFighter\Cabs\Geolocation\GeocodingService;
use LegacyFighter\Cabs\Invocing\InvoiceGenerator;
use LegacyFighter\Cabs\Loyalty\AwardsService;
use LegacyFighter\Cabs\Pricing\Tariffs;
use LegacyFighter\Cabs\Ride\Details\TransitDetailsFacade;
use LegacyFighter\Cabs\Ride\Events\TransitCompleted;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RideService
{
    public function __construct(
        private RequestTransitService $requestTransitService,
        private DriverRepository $driverRepository,
        private TransitRepository $transitRepository,
        private ClientRepository $clientRepository,
        private InvoiceGenerator $invoiceGenerator,
        private DistanceCalculator $distanceCalculator,
        private DriverService $driverService,
        private GeocodingService $geocodingService,
        private AddressRepository $addressRepository,
        private DriverFeeService $driverFeeService,
        private TransitDetailsFacade $transitDetailsFacade,
        private Clock $clock,
        private AwardsService $awardsService,
        private EventDispatcherInterface $dispatcher,
        private Tariffs $tariffs,
        private RequestForTransitRepository $requestForTransitRepository,
        private DriverAssignmentFacade $driverAssignmentFacade,
        private TransitDemandRepository $transitDemandRepository
    )
    { }

    public function createTransit(TransitDTO $transitDTO): TransitDTO
    {
        return $this->createTransitFrom($transitDTO->getClientDTO()->getId(), $transitDTO->getFrom(), $transitDTO->getTo(), $transitDTO->getCarClass());
    }

    public function createTransitFrom(int $clientId, AddressDTO $from, AddressDTO $to, ?string $carClass): TransitDTO
    {
        $client = $this->clientRepository->getOne($clientId);
        $from = $this->addressFromDto($from);
        $to = $this->addressFromDto($to);
        $now = $this->clock->now();
        $requestForTransit = $this->requestTransitService->createRequestForTransit($from, $to);
        $this->transitDetailsFacade->transitRequested($now, $requestForTransit->getRequestUuid(), $from, $to, $requestForTransit->getDistance(), $client, $carClass, $requestForTransit->getEstimatedPrice(), $requestForTransit->getTariff());
        return $this->loadTransit($requestForTransit->getRequestUuid());
    }

    private function addressFromDto(AddressDTO $addressDTO): Address
    {
        $address = $addressDTO->toAddressEntity();
        return $this->addressRepository->save($address);
    }

    public function changeTransitAddressFromNew(Uuid $requestUuid, Address $newAddress): void
    {
        $newAddress = $this->addressRepository->save($newAddress);
        $transitDemand = $this->transitDemandRepository->findByRequestUuid($requestUuid);
        $transitDetails = $this->transitDetailsFacade->findByUuid($requestUuid);

        if($transitDemand === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
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
        $transitDemand->changePickupTo($distanceInKMeters);
        $this->transitDetailsFacade->pickupChangedTo($requestUuid, $newAddress, $distance);
        $this->driverAssignmentFacade->notifyProposedDriversAboutChangedDestination($requestUuid);
    }

    public function changeTransitAddressToNew(Uuid $requestUuid, Address $newAddress): void
    {
        $newAddress = $this->addressRepository->save($newAddress);
        $requestForTransit = $this->requestForTransitRepository->findByRequestUuid($requestUuid);
        $transitDetails = $this->transitDetailsFacade->findByUuid($requestUuid);

        if($requestForTransit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }

        // FIXME later: add some exceptions handling
        $geoFrom = $this->geocodingService->geocodeAddress($transitDetails->from->toAddressEntity());
        $geoTo = $this->geocodingService->geocodeAddress($newAddress);

        $distance = Distance::ofKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));
        $transit = $this->transitRepository->findByTransitRequestUuid($requestUuid);
        $transit?->changeDestination($distance);
        $this->driverAssignmentFacade->notifyAssignedDriverAboutChangedDestination($requestUuid);
        $this->transitDetailsFacade->destinationChanged($requestUuid, $newAddress, $distance);
    }

    public function changeTransitAddressTo(Uuid $requestUuid, AddressDTO $newAddress): void
    {
        $this->changeTransitAddressToNew($requestUuid, $this->addressFromDto($newAddress));
    }

    public function changeTransitAddressFrom(Uuid $requestUuid, AddressDTO $newAddress): void
    {
        $this->changeTransitAddressFromNew($requestUuid, $this->addressFromDto($newAddress));
    }

    public function cancelTransit(Uuid $requestUuid): void
    {
        $transit = $this->requestForTransitRepository->findByRequestUuid($requestUuid);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }

        $transitDemand = $this->transitDemandRepository->findByRequestUuid($requestUuid);
        if($transitDemand !== null) {
            $transitDemand->cancel();
            $this->driverAssignmentFacade->cancel($requestUuid);
        }
        $this->transitDetailsFacade->transitCancelled($requestUuid);
    }

    public function publishTransit(Uuid $requestUuid): void
    {
        $requestFor = $this->requestForTransitRepository->findByRequestUuid($requestUuid);
        $transitDetailsDto = $this->transitDetailsFacade->findByUuid($requestUuid);

        if($requestFor === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }

        $now = $this->clock->now();
        $this->transitDemandRepository->save(new TransitDemand($requestUuid));
        $this->driverAssignmentFacade->createAssignment($requestUuid, $transitDetailsDto->from, $transitDetailsDto->carType, $now);
        $this->transitDetailsFacade->transitPublished($requestUuid, $now);
    }

    // Abandon hope all ye who enter here...
    public function findDriversForTransit(Uuid $requestUuid): void
    {
        $transitDetailsDto = $this->transitDetailsFacade->findByUuid($requestUuid);
        $involvedDriversSummary = $this->driverAssignmentFacade->searchForPossibleDrivers($requestUuid, $transitDetailsDto->from, $transitDetailsDto->carType);
        $this->transitDetailsFacade->driversAreInvolved($requestUuid, $involvedDriversSummary);
    }

    public function acceptTransit(int $driverId, Uuid $requestUuid): void
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        }
        $transitDemand = $this->transitDemandRepository->findByRequestUuid($requestUuid);
        if($transitDemand === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }

        if($this->driverAssignmentFacade->isDriverAssigned($requestUuid)) {
            throw new \InvalidArgumentException('Driver already assigned, requestUUID = '.$requestUuid);
        }

        $now = $this->clock->now();
        $transitDemand->accept();
        $this->driverAssignmentFacade->acceptTransit($requestUuid, $driver);
        $this->transitDetailsFacade->transitAccepted($requestUuid, $now, $driverId);
        $this->driverRepository->save($driver);
    }

    public function startTransit(int $driverId, Uuid $requestUuid): void
    {
        $driver = $this->driverRepository->getOne($driverId);

        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        }

        $transitDemand = $this->transitDemandRepository->findByRequestUuid($requestUuid);

        if($transitDemand === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }

        if(!$this->driverAssignmentFacade->isDriverAssigned($requestUuid)) {
            throw new \InvalidArgumentException('Driver not assigned, requestUUID = '.$requestUuid);
        }

        $now = $this->clock->now();
        $transit = $this->transitRepository->save(new Transit($this->tariffs->choose($now), $requestUuid));
        $this->transitDetailsFacade->transitStarted($requestUuid, $transit->getId(), $now);
    }

    public function rejectTransit(int $driverId, Uuid $requestUuid): void
    {
        $driver = $this->driverRepository->getOne($driverId);

        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        }

        $this->driverAssignmentFacade->rejectTransit($requestUuid, $driverId);
    }

    public function completeTransit(int $driverId, Uuid $requestUuid, AddressDTO $destinationAddress): void
    {
        $this->completeTransitFrom($driverId, $requestUuid, $destinationAddress->toAddressEntity());
    }

    public function completeTransitFrom(int $driverId, Uuid $requestUuid, Address $destinationAddress): void
    {
        $destinationAddress = $this->addressRepository->save($destinationAddress);
        $driver = $this->driverRepository->getOne($driverId);

        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        }

        $transit = $this->transitRepository->findByTransitRequestUuid($requestUuid);
        $transitDetails = $this->transitDetailsFacade->findByUuid($requestUuid);

        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }

        // FIXME later: add some exceptions handling
        $geoFrom = $this->geocodingService->geocodeAddress($transitDetails->from->toAddressEntity());
        $geoTo = $this->geocodingService->geocodeAddress($destinationAddress);
        $distance = Distance::ofKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));
        $now = $this->clock->now();
        $finalPrice = $transit->completeAt($distance);

        $driverFee = $this->driverFeeService->calculateDriverFee($finalPrice, $driverId);
        $driver->setOccupied(false);
        $this->driverRepository->save($driver);
        $this->awardsService->registerMiles($transitDetails->client->getId(), $transit->getId());
        $this->transitRepository->save($transit);
        $this->transitDetailsFacade->transitCompleted($requestUuid, $now, $finalPrice, $driverFee);
        $this->invoiceGenerator->generate($finalPrice->toInt(), $transitDetails->client->getName() . ' ' . $transitDetails->client->getLastName());
        $this->dispatcher->dispatch(new TransitCompleted(
            $transitDetails->client->getId(),
            $transit->getId(),
            $transitDetails->from->getHash(),
            $transitDetails->to->getHash(),
            $transitDetails->started,
            $now
        ));

    }

    public function loadTransit(Uuid $requestUuid): TransitDTO
    {
        $involvedDriversSummary = $this->driverAssignmentFacade->loadInvolvedDrivers($requestUuid);

        return TransitDTO::from(
            $this->transitDetailsFacade->findByUuid($requestUuid),
            $this->driverService->loadDrivers($involvedDriversSummary->proposedDrivers),
            $involvedDriversSummary->assignedDriver
        );
    }

    public function loadTransitBy(int $id): TransitDTO
    {
        return $this->loadTransit($this->getRequestUuid($id));
    }

    public function getRequestUuid(int $requestId): Uuid
    {
        return $this->requestForTransitRepository->getOne($requestId)->getRequestUuid();
    }
}
