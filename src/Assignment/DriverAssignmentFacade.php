<?php

namespace LegacyFighter\Cabs\Assignment;

use LegacyFighter\Cabs\CarFleet\CarTypeService;
use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Notification\DriverNotificationService;
use LegacyFighter\Cabs\Tracking\DriverTrackingService;
use Symfony\Component\Uid\Uuid;

class DriverAssignmentFacade
{
    public function __construct(
        private DriverAssignmentRepository $driverAssignmentRepository,
        private Clock $clock,
        private CarTypeService $carTypeService,
        private DriverTrackingService $driverTrackingService,
        private DriverNotificationService $driverNotificationService
    )
    {
    }

    public function createAssignment(Uuid $transitRequestUUID, AddressDTO $from, string $carClass, \DateTimeImmutable $when): InvolvedDriversSummary
    {
        $this->driverAssignmentRepository->save(new DriverAssignment($transitRequestUUID, $when));
        return $this->searchForPossibleDrivers($transitRequestUUID, $from, $carClass);
    }

    public function searchForPossibleDrivers(Uuid $transitRequestUUID, AddressDTO $from, string $carClass): InvolvedDriversSummary
    {
        $driverAssignment = $this->find($transitRequestUUID);
        if($driverAssignment !== null) {
            $distanceToCheck = 0;

            // Tested on production, works as expected.
            // If you change this code and the system will collapse AGAIN, I'll find you...
            while (true) {
                if($driverAssignment->getAwaitingDriversResponses() > 4) {
                    return InvolvedDriversSummary::noneFound();
                }
                $distanceToCheck++;
                // FIXME: to refactor when the final business logic will be determined
                if($driverAssignment->shouldNotWaitForDriverAnyMore($this->clock->now()) || $distanceToCheck >= 20) {
                    $driverAssignment->failDriverAssignment();
                    $this->driverAssignmentRepository->save($driverAssignment);
                    return InvolvedDriversSummary::noneFound();
                }

                $carClasses = $this->choosePossibleCarClasses($carClass);
                if($carClasses === []) {
                    return InvolvedDriversSummary::noneFound();
                }

                $driversAvgPositions = $this->driverTrackingService->findActiveDriversNearbyAddress($from, Distance::ofKm($distanceToCheck), $carClasses);
                if($driversAvgPositions === []) {
                    //next iteration
                    continue;
                }

                // Iterate across average driver positions
                foreach ($driversAvgPositions as $driverAvgPosition) {
                    if($driverAssignment->canProposeTo($driverAvgPosition->getDriverId())) {
                        $driverAssignment->proposeTo($driverAvgPosition->getDriverId());
                        $this->driverNotificationService->notifyAboutPossibleTransitBy($driverAvgPosition->getDriverId(), $transitRequestUUID);
                    }
                }

                $this->driverAssignmentRepository->save($driverAssignment);
                return $this->loadInvolvedDriversFrom($driverAssignment);
            }
        } else {
            throw new \InvalidArgumentException(sprintf('Transit does not exist, id = %s', $transitRequestUUID));
        }
    }

    public function acceptTransit(Uuid $transitRequestUUID, Driver $driver): InvolvedDriversSummary
    {
        $driverAssigment = $this->find($transitRequestUUID);
        $driverAssigment->acceptBy($driver->getId());
        $driver->setOccupied(true);
        return $this->loadInvolvedDriversFrom($driverAssigment);
    }

    public function rejectTransit(Uuid $transitRequestUUID, int $driverId): InvolvedDriversSummary
    {
        $driverAssigment = $this->find($transitRequestUUID);
        if($driverAssigment === null) {
            throw new \InvalidArgumentException('Assignment does not exist, id = ', $transitRequestUUID);
        }
        $driverAssigment->rejectBy($driverId);
        return $this->loadInvolvedDriversFrom($driverAssigment);
    }

    public function isDriverAssigned(Uuid $transitRequestUUID): bool {
        return $this->driverAssignmentRepository->findByRequestIdAndStatus($transitRequestUUID, AssignmentStatus::ON_THE_WAY) !== null;
    }

    public function cancel(Uuid $transitRequestUUID): void
    {
        $driverAssigment = $this->find($transitRequestUUID);
        if($driverAssigment !== null) {
            $driverAssigment->cancel();
            $this->notifyAboutCancelledDestination($driverAssigment, $transitRequestUUID);
        }
    }

    public function notifyProposedDriversAboutChangedDestination(Uuid $transitRequestUUID): void
    {
        $driverAssigment = $this->find($transitRequestUUID);
        foreach ($driverAssigment->getProposedDrivers() as $driverId) {
            $this->driverNotificationService->notifyAboutChangedTransitAddressBy($driverId, $transitRequestUUID);
        }
    }

    public function notifyAssignedDriverAboutChangedDestination(Uuid $transitRequestUUID): void
    {
        $driverAssigment = $this->find($transitRequestUUID);
        if($driverAssigment !== null && $driverAssigment->getAssignedDriver() !== null) {
            $this->driverNotificationService->notifyAboutChangedTransitAddressBy($driverAssigment->getAssignedDriver(), $transitRequestUUID);
            foreach ($driverAssigment->getProposedDrivers() as $driver) {
                $this->driverNotificationService->notifyAboutChangedTransitAddressBy($driver, $transitRequestUUID);
            }
        }
    }

    public function loadInvolvedDrivers(Uuid $transitRequestUUID): InvolvedDriversSummary
    {
        $driverAssigment = $this->find($transitRequestUUID);
        if($driverAssigment===null) {
            return InvolvedDriversSummary::noneFound();
        }

        return $this->loadInvolvedDriversFrom($driverAssigment);
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

    private function find(Uuid $transitRequestUUID): ?DriverAssignment
    {
        return $this->driverAssignmentRepository->findByRequestId($transitRequestUUID);
    }

    private function loadInvolvedDriversFrom(DriverAssignment $driverAssignment): InvolvedDriversSummary
    {
        return new InvolvedDriversSummary(
            $driverAssignment->getProposedDrivers(),
            $driverAssignment->getDriversRejections(),
            $driverAssignment->getAssignedDriver(),
            $driverAssignment->getStatus()
        );
    }

    private function notifyAboutCancelledDestination(DriverAssignment $driverAssignment, Uuid $transitRequestUUID): void
    {
        $assignedDriver = $driverAssignment->getAssignedDriver();
        if ($assignedDriver !== null) {
            $this->driverNotificationService->notifyAboutCancelledTransitBy($assignedDriver, $transitRequestUUID);
        }
    }
}
