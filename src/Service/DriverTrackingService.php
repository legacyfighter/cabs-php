<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverPosition;
use LegacyFighter\Cabs\Repository\DriverPositionRepository;
use LegacyFighter\Cabs\Repository\DriverRepository;

class DriverTrackingService
{
    private DriverPositionRepository $driverPositionRepository;
    private DriverRepository $driverRepository;
    private DistanceCalculator $distanceCalculator;
    private Clock $clock;

    public function __construct(DriverPositionRepository $driverPositionRepository, DriverRepository $driverRepository, DistanceCalculator $distanceCalculator, Clock $clock)
    {
        $this->driverPositionRepository = $driverPositionRepository;
        $this->driverRepository = $driverRepository;
        $this->distanceCalculator = $distanceCalculator;
        $this->clock = $clock;
    }

    public function registerPosition(int $driverId, float $latitude, float $longitude): DriverPosition
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
        $driverPosition->setSeenAt(new \DateTimeImmutable());
        $driverPosition->setLatitude($latitude);
        $driverPosition->setLongitude($longitude);
        return $this->driverPositionRepository->save($driverPosition);
    }

    public function calculateTravelledDistance(int $driverId, \DateTimeImmutable $from, \DateTimeImmutable $to): float
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver===null) {
            throw new \InvalidArgumentException('Driver does not exists, id = '.$driverId);
        }
        $positions = $this->driverPositionRepository->findByDriverAndSeenAtBetweenOrderBySeenAtAsc($driver, $from, $to);
        $distanceTravelled = 0.0;

        if(count($positions)>1) {
            $previousPosition = array_shift($positions);

            foreach ($positions as $position) {
                $distanceTravelled += $this->distanceCalculator->calculateByGeo(
                    $previousPosition->getLatitude(),
                    $previousPosition->getLongitude(),
                    $position->getLatitude(),
                    $position->getLongitude()
                );

                $previousPosition = $position;
            }
        }

        return $distanceTravelled;
    }
}
