<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\CarFleet\CarType;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\DriverFleet\DriverAttribute;
use LegacyFighter\Cabs\DriverFleet\DriverAttributeRepository;
use LegacyFighter\Cabs\DriverFleet\DriverFee;
use LegacyFighter\Cabs\DriverFleet\DriverFeeRepository;
use LegacyFighter\Cabs\DriverFleet\DriverService;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Tracking\DriverSessionService;
use LegacyFighter\Cabs\Tracking\DriverTrackingService;

class DriverFixture
{
    public function __construct(
        private DriverSessionService $driverSessionService,
        private DriverTrackingService $driverTrackingService,
        private DriverService $driverService,
        private DriverFeeRepository $feeRepository,
        private DriverAttributeRepository $driverAttributeRepository
    )
    {
    }

    public function aDriver(
        string $status = Driver::STATUS_ACTIVE,
        string $name = 'Janusz',
        string $lastName = 'Kowalski',
        string $license = 'FARME100165AB5EW'
    ): Driver
    {
        return $this->driverService->createDriver($license, $lastName, $name, Driver::TYPE_REGULAR, $status, '');
    }

    public function aNearbyDriver(string $plateNumber, float $latitude = 1.0, float $longitude = 1.0, $carType = CarType::CAR_CLASS_VAN, ?\DateTimeImmutable $when = null): Driver
    {
        $driver = $this->aDriver();
        $this->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);
        $this->driverSessionService->logIn($driver->getId(), $plateNumber, $carType, 'brand');
        $this->driverIsAtGeoLocalization($driver->getId(), $latitude, $longitude, $when ?? new \DateTimeImmutable());
        return $driver;
    }

    public function driverIsAtGeoLocalization(int $driverId, float $latitude, float $longitude, \DateTimeImmutable $when): void
    {
        $this->driverTrackingService->registerPosition($driverId, $latitude, $longitude, $when);
    }

    public function driverHasFee(Driver $driver, string $feeType, int $amount, ?int $min = null): DriverFee
    {
        $driverFee = new DriverFee($feeType, $driver, $amount, $min === null ? Money::zero() : Money::from($min));
        return $this->feeRepository->save($driverFee);
    }

    public function driverHasAttribute(Driver $driver, string $name, string $value): void
    {
        $this->driverAttributeRepository->save(new DriverAttribute($name, $value, $driver));
    }
}
