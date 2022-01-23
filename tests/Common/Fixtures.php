<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverFee;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repository\DriverFeeRepository;
use LegacyFighter\Cabs\Repository\TransitRepository;
use LegacyFighter\Cabs\Service\DriverService;

class Fixtures
{
    private TransitRepository $transitRepository;
    private DriverFeeRepository $feeRepository;
    private DriverService $driverService;

    public function __construct(TransitRepository $transitRepository, DriverFeeRepository $feeRepository, DriverService $driverService)
    {
        $this->transitRepository = $transitRepository;
        $this->feeRepository = $feeRepository;
        $this->driverService = $driverService;
    }

    public function aDriver(): Driver
    {
        return $this->driverService->createDriver('FARME100165AB5EW', 'Kowalski', 'Janusz', Driver::TYPE_REGULAR, Driver::STATUS_ACTIVE, '');
    }

    public function driverHasFee(Driver $driver, string $feeType, int $amount, ?int $min = null): DriverFee
    {
        $driverFee = new DriverFee($feeType, $driver, $amount, $min === null ? Money::zero() : Money::from($min));
        return $this->feeRepository->save($driverFee);
    }

    public function aTransit(Driver $driver, int $price, ?\DateTimeImmutable $when = null): Transit
    {
        $transit = new Transit();
        $transit->setStatus(Transit::STATUS_DRAFT);
        $transit->setPrice(Money::from($price));
        $transit->setDriver($driver);
        $transit->setDateTime($when ?? new \DateTimeImmutable());
        return $this->transitRepository->save($transit);
    }
}
