<?php

namespace LegacyFighter\Cabs\Ride;

use LegacyFighter\Cabs\Assignment\DriverAssignmentFacade;
use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Crm\ClientRepository;
use LegacyFighter\Cabs\DriverFleet\DriverFeeService;
use LegacyFighter\Cabs\DriverFleet\DriverService;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Address\AddressRepository;
use LegacyFighter\Cabs\Invocing\InvoiceGenerator;
use LegacyFighter\Cabs\Loyalty\AwardsService;
use LegacyFighter\Cabs\Ride\Details\TransitDetailsFacade;
use LegacyFighter\Cabs\Ride\Events\TransitCompleted;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

// If this class will still be here in 2022 I will quit.
class RideService
{
    public function __construct(
        private RequestTransitService $requestTransitService,
        private ChangePickupService $changePickupService,
        private ChangeDestinationService $changeDestinationService,
        private DemandService $demandService,
        private CompleteTransitService $completeTransitService,
        private StartTransitService $startTransitService,
        private ClientRepository $clientRepository,
        private InvoiceGenerator $invoiceGenerator,
        private DriverService $driverService,
        private AddressRepository $addressRepository,
        private DriverFeeService $driverFeeService,
        private TransitDetailsFacade $transitDetailsFacade,
        private Clock $clock,
        private AwardsService $awardsService,
        private EventDispatcherInterface $dispatcher,
        private DriverAssignmentFacade $driverAssignmentFacade,
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
        if($this->driverAssignmentFacade->isDriverAssigned($requestUuid)) {
            throw new \InvalidArgumentException('Driver already assigned, requestUUID = '.$requestUuid);
        }
        $transitDetails = $this->transitDetailsFacade->findByUuid($requestUuid);
        $newDistance = $this->changePickupService->changeTransitAddressFrom($requestUuid, $newAddress, $transitDetails->from->toAddressEntity());
        $this->transitDetailsFacade->pickupChangedTo($requestUuid, $newAddress, $newDistance);
        $this->driverAssignmentFacade->notifyProposedDriversAboutChangedDestination($requestUuid);
    }

    public function changeTransitAddressToNew(Uuid $requestUuid, Address $newAddress): void
    {
        $transitDetails = $this->transitDetailsFacade->findByUuid($requestUuid);
        $distance = $this->changeDestinationService->changeTransitAddressTo($requestUuid, $newAddress, $transitDetails->from->toAddressEntity());
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
        $transitDetailsDTO = $this->transitDetailsFacade->findByUuid($requestUuid);
        if($transitDetailsDTO === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }
        $this->demandService->cancelDemand($requestUuid);
        $this->driverAssignmentFacade->cancel($requestUuid);
        $this->transitDetailsFacade->transitCancelled($requestUuid);
    }

    public function publishTransit(Uuid $requestUuid): void
    {
        $transitDetailsDTO = $this->transitDetailsFacade->findByUuid($requestUuid);
        if($transitDetailsDTO === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }
        $this->demandService->publishDemand($requestUuid);
        $this->driverAssignmentFacade->startAssigningDrivers($requestUuid, $transitDetailsDTO->from, $transitDetailsDTO->carType, $this->clock->now());
        $this->transitDetailsFacade->transitPublished($requestUuid, $this->clock->now());
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
        if(!$this->driverService->exists($driverId)) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        } else {
            if($this->driverAssignmentFacade->isDriverAssigned($requestUuid)) {
                throw new \InvalidArgumentException('Driver already assigned, requestUUID = '.$requestUuid);
            }
            $this->demandService->acceptDemand($requestUuid);
            $this->driverAssignmentFacade->acceptTransit($requestUuid, $driverId);
            $this->driverService->markNotOccupied($driverId);
            $this->transitDetailsFacade->transitAccepted($requestUuid, $this->clock->now(), $driverId);
        }
    }

    public function startTransit(int $driverId, Uuid $requestUuid): void
    {
        if(!$this->driverService->exists($driverId)) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        }

        if(!$this->demandService->existsFor($requestUuid)) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }

        if(!$this->driverAssignmentFacade->isDriverAssigned($requestUuid)) {
            throw new \InvalidArgumentException('Driver not assigned, requestUUID = '.$requestUuid);
        }

        $now = $this->clock->now();
        $transit = $this->startTransitService->start($requestUuid);
        $this->transitDetailsFacade->transitStarted($requestUuid, $transit->getId(), $now);
    }

    public function rejectTransit(int $driverId, Uuid $requestUuid): void
    {
        if(!$this->driverService->exists($driverId)) {
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
        $transitDetails = $this->transitDetailsFacade->findByUuid($requestUuid);
        if(!$this->driverService->exists($driverId)) {
            throw new \InvalidArgumentException('Driver does not exist, id = '.$driverId);
        }
        $from = $this->addressRepository->getByHash($transitDetails->from->getHash());
        $to = $this->addressRepository->getByHash($destinationAddress->getHash());
        $finalPrice = $this->completeTransitService->completeTransit($driverId, $requestUuid, $from, $to);
        $driverFee = $this->driverFeeService->calculateDriverFee($finalPrice, $driverId);
        $this->driverService->markNotOccupied($driverId);
        $this->transitDetailsFacade->transitCompleted($requestUuid, $this->clock->now(), $finalPrice, $driverFee);
        $this->awardsService->registerMiles($transitDetails->client->getId(), $transitDetails->transitId);
        $this->invoiceGenerator->generate($finalPrice->toInt(), $transitDetails->client->getName() . ' ' . $transitDetails->client->getLastName());
        $this->dispatcher->dispatch(new TransitCompleted(
            $transitDetails->client->getId(),
            $transitDetails->transitId,
            $transitDetails->from->getHash(),
            $transitDetails->to->getHash(),
            $transitDetails->started,
            $this->clock->now()
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
        return $this->requestTransitService->findCalculationUUID($requestId);
    }
}
