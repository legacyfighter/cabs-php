<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\DriverFleet\DriverReport\TravelledDistance\TravelledDistanceService;
use LegacyFighter\Cabs\DriverFleet\DriverRepository;
use LegacyFighter\Cabs\Entity\DriverPosition;
use LegacyFighter\Cabs\Repository\DriverPositionRepository;

class DriverTrackingService
{
    public function __construct(
        private DriverPositionRepository $driverPositionRepository,
        private DriverRepository $driverRepository,
        private TravelledDistanceService $travelledDistanceService
    )
    {
    }

    public function registerPosition(int $driverId, float $latitude, float $longitude, \DateTimeImmutable $seenAt): DriverPosition
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver===null) {
            throw new \InvalidArgumentException('Driver does not exists, id = '.$driverId);
        }
        if($driver->getStatus() !== Driver::STATUS_ACTIVE) {
            throw new \InvalidArgumentException('Driver is not active, cannot register position, id = '.$driverId);
        }
        $driverPosition = $this->driverPositionRepository->save(new DriverPosition($driver, $latitude, $longitude, $seenAt));
        $this->travelledDistanceService->addPosition($driverId, $latitude, $longitude, $seenAt);
        return $driverPosition;
    }

    public function calculateTravelledDistance(int $driverId, \DateTimeImmutable $from, \DateTimeImmutable $to): Distance
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver===null) {
            throw new \InvalidArgumentException('Driver does not exists, id = '.$driverId);
        }

        return $this->travelledDistanceService->calculateDistance($driverId, $from, $to);
    }
}
