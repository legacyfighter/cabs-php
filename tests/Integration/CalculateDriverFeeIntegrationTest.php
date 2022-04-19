<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\DriverFleet\DriverFee;
use LegacyFighter\Cabs\DriverFleet\DriverFeeService;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CalculateDriverFeeIntegrationTest extends KernelTestCase
{
    private Fixtures $fixtures;
    private DriverFeeService $driverFeeService;

    protected function setUp(): void
    {
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
        $this->driverFeeService = $this->getContainer()->get(DriverFeeService::class);
    }

    /**
     * @test
     */
    public function shouldCalculateDriversFlatFee(): void
    {
        //given
        $driver = $this->fixtures->aDriver();
        //and
        $this->fixtures->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);

        //when
        $fee = $this->driverFeeService->calculateDriverFee(Money::from(60), $driver->getId());

        //then
        self::assertEquals(Money::from(50), $fee);
    }

    /**
     * @test
     */
    public function shouldCalculateDriversPercentageFee(): void
    {
        //given
        $driver = $this->fixtures->aDriver();
        //and
        $this->fixtures->driverHasFee($driver, DriverFee::TYPE_PERCENTAGE, 50);

        //when
        $fee = $this->driverFeeService->calculateDriverFee(Money::from(80), $driver->getId());

        //then
        self::assertEquals(Money::from(40), $fee);
    }

    /**
     * @test
     */
    public function shouldUseMinimumFee(): void
    {
        //given
        $driver = $this->fixtures->aDriver();
        //and
        $this->fixtures->driverHasFee($driver, DriverFee::TYPE_PERCENTAGE, 7, 5);

        //when
        $fee = $this->driverFeeService->calculateDriverFee(Money::from(10), $driver->getId());

        //then
        self::assertEquals(Money::from(5), $fee);
    }
}
