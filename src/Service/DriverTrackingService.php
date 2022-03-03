<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverPosition;
use LegacyFighter\Cabs\Repository\DriverPositionRepository;
use LegacyFighter\Cabs\Repository\DriverRepository;
use LegacyFighter\Cabs\TravelledDistance\TravelledDistanceService;

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
        $driverPosition = new DriverPosition();
        $driverPosition->setDriver($driver);
        $driverPosition->setSeenAt($seenAt);
        $driverPosition->setLatitude($latitude);
        $driverPosition->setLongitude($longitude);
        $driverPosition = $this->driverPositionRepository->save($driverPosition);
        $this->travelledDistanceService->addPosition($driverPosition);
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
