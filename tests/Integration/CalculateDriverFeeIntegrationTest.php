<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverFee;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repository\DriverFeeRepository;
use LegacyFighter\Cabs\Repository\TransitRepository;
use LegacyFighter\Cabs\Service\DriverFeeService;
use LegacyFighter\Cabs\Service\DriverService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CalculateDriverFeeIntegrationTest extends KernelTestCase
{
    private DriverFeeService $driverFeeService;
    private DriverFeeRepository $feeRepository;
    private TransitRepository $transitRepository;
    private DriverService $driverService;

    protected function setUp(): void
    {
        $this->driverFeeService = $this->getContainer()->get(DriverFeeService::class);
        $this->feeRepository = $this->getContainer()->get(DriverFeeRepository::class);
        $this->transitRepository = $this->getContainer()->get(TransitRepository::class);
        $this->driverService = $this->getContainer()->get(DriverService::class);
    }

    /**
     * @test
     */
    public function shouldCalculateDriversFlatFee(): void
    {
        //given
        $driver = $this->aDriver();
        //and
        $transit = $this->aTransit($driver, 60);
        //and
        $this->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);

        //when
        $fee = $this->driverFeeService->calculateDriverFee($transit->getId());

        //then
        self::assertEquals(Money::from(50), $fee);
    }

    /**
     * @test
     */
    public function shouldCalculateDriversPercentageFee(): void
    {
        //given
        $driver = $this->aDriver();
        //and
        $transit = $this->aTransit($driver, 80);
        //and
        $this->driverHasFee($driver, DriverFee::TYPE_PERCENTAGE, 50);

        //when
        $fee = $this->driverFeeService->calculateDriverFee($transit->getId());

        //then
        self::assertEquals(Money::from(40), $fee);
    }

    /**
     * @test
     */
    public function shouldUseMinimumFee(): void
    {
        //given
        $driver = $this->aDriver();
        //and
        $transit = $this->aTransit($driver, 10);
        //and
        $this->driverHasFeeWithMin($driver, DriverFee::TYPE_PERCENTAGE, 7, 5);

        //when
        $fee = $this->driverFeeService->calculateDriverFee($transit->getId());

        //then
        self::assertEquals(Money::from(5), $fee);
    }

    private function aDriver(): Driver
    {
        return $this->driverService->createDriver('FARME100165AB5EW', 'Kowalski', 'Janusz', Driver::TYPE_REGULAR, Driver::STATUS_ACTIVE, '');
    }

    private function driverHasFeeWithMin(Driver $driver, string $feeType, int $amount, int $min): DriverFee
    {
        $driverFee = new DriverFee($feeType, $driver, $amount, Money::from($min));
        return $this->feeRepository->save($driverFee);
    }

    private function driverHasFee(Driver $driver, string $feeType, int $amount): DriverFee
    {
        return $this->driverHasFeeWithMin($driver, $feeType, $amount, 0);
    }

    private function aTransit(Driver $driver, int $price): Transit
    {
        $transit = new Transit();
        $transit->setStatus(Transit::STATUS_DRAFT);
        $transit->setPrice(Money::from($price));
        $transit->setDriver($driver);
        $transit->setDateTime(new \DateTimeImmutable('2020-10-20'));
        return $this->transitRepository->save($transit);
    }
}
