<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Entity\DriverFee;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Service\DriverService;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CalculateDriverPeriodicPaymentsIntegrationTest extends KernelTestCase
{
    private DriverService $driverService;
    private Fixtures $fixtures;

    protected function setUp(): void
    {
        $this->driverService = $this->getContainer()->get(DriverService::class);
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
    }

    /**
     * @test
     */
    public function calculateMonthlyPayment(): void
    {
        //given
        $driver = $this->fixtures->aDriver();
        //and
        $this->fixtures->aTransit($driver, 60, new \DateTimeImmutable('2000-10-01 06:30'));
        $this->fixtures->aTransit($driver, 70, new \DateTimeImmutable('2000-10-10 02:30'));
        $this->fixtures->aTransit($driver, 80, new \DateTimeImmutable('2000-10-30 06:30'));
        $this->fixtures->aTransit($driver, 60, new \DateTimeImmutable('2000-11-10 01:30'));
        $this->fixtures->aTransit($driver, 30, new \DateTimeImmutable('2000-11-10 01:30'));
        $this->fixtures->aTransit($driver, 15, new \DateTimeImmutable('2000-12-10 02:30'));

        //and
        $this->fixtures->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);

        //when
        $feeOctober = $this->driverService->calculateDriverMonthlyPayment($driver->getId(), 2000, 10);
        //then
        self::assertEquals(Money::from(180), $feeOctober);

        //when
        $feeNovember = $this->driverService->calculateDriverMonthlyPayment($driver->getId(), 2000, 11);
        //then
        self::assertEquals(Money::from(70), $feeNovember);

        //when
        $feeDecember = $this->driverService->calculateDriverMonthlyPayment($driver->getId(), 2000, 12);
        //then
        self::assertEquals(Money::from(5), $feeDecember);
    }

    /**
     * @test
     */
    public function calculateYearlyPayment(): void
    {
        //given
        $driver = $this->fixtures->aDriver();
        //and
        $this->fixtures->aTransit($driver, 60, new \DateTimeImmutable('2000-10-01 06:30'));
        $this->fixtures->aTransit($driver, 70, new \DateTimeImmutable('2000-10-10 02:30'));
        $this->fixtures->aTransit($driver, 80, new \DateTimeImmutable('2000-10-30 06:30'));
        $this->fixtures->aTransit($driver, 60, new \DateTimeImmutable('2000-11-10 01:30'));
        $this->fixtures->aTransit($driver, 30, new \DateTimeImmutable('2000-11-10 01:30'));
        $this->fixtures->aTransit($driver, 15, new \DateTimeImmutable('2000-12-10 02:30'));

        //and
        $this->fixtures->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);

        //when
        $payments = $this->driverService->calculateDriverYearlyPayment($driver->getId(), 2000);

        //then
        self::assertEquals(Money::zero(), $payments[1]);
        self::assertEquals(Money::zero(), $payments[2]);
        self::assertEquals(Money::zero(), $payments[3]);
        self::assertEquals(Money::zero(), $payments[4]);
        self::assertEquals(Money::zero(), $payments[5]);
        self::assertEquals(Money::zero(), $payments[6]);
        self::assertEquals(Money::zero(), $payments[7]);
        self::assertEquals(Money::zero(), $payments[8]);
        self::assertEquals(Money::zero(), $payments[9]);
        self::assertEquals(Money::from(180), $payments[10]);
        self::assertEquals(Money::from(70), $payments[11]);
        self::assertEquals(Money::from(5), $payments[12]);
    }
}
