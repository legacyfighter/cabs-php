<?php

namespace LegacyFighter\Cabs\Tests\Unit\Entity;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\Entity\Tariff;
use LegacyFighter\Cabs\Money\Money;
use PHPUnit\Framework\TestCase;

class TariffTest extends TestCase
{
    /**
     * @test
     */
    public function regularTariffShouldBeDisplayedAndCalculated(): void
    {
        //given
        $tariff = Tariff::ofTime(new \DateTimeImmutable('2021-04-16 08:30'));

        //expect
        self::assertEquals(Money::from(2900), $tariff->calculateCost(Distance::ofKm(20))); //29.00
        self::assertEquals('Standard', $tariff->getName());
        self::assertEquals(1.0, $tariff->getKmRate());
    }

    /**
     * @test
     */
    public function sundayTariffShouldBeDisplayedAndCalculated(): void
    {
        //given
        $tariff = Tariff::ofTime(new \DateTimeImmutable('2021-04-18 08:30'));

        //expect
        self::assertEquals(Money::from(3800), $tariff->calculateCost(Distance::ofKm(20))); //38.00
        self::assertEquals('Weekend', $tariff->getName());
        self::assertEquals(1.5, $tariff->getKmRate());
    }

    /**
     * @test
     */
    public function newYearsEveTariffShouldBeDisplayedAndCalculated(): void
    {
        //given
        $tariff = Tariff::ofTime(new \DateTimeImmutable('2021-12-31 08:30'));

        //expect
        self::assertEquals(Money::from(8100), $tariff->calculateCost(Distance::ofKm(20))); //81.00
        self::assertEquals('Sylwester', $tariff->getName());
        self::assertEquals(3.5, $tariff->getKmRate());
    }

    /**
     * @test
     */
    public function saturdayTariffShouldBeDisplayedAndCalculated(): void
    {
        //given
        $tariff = Tariff::ofTime(new \DateTimeImmutable('2021-04-17 08:30'));

        //expect
        self::assertEquals(Money::from(3800), $tariff->calculateCost(Distance::ofKm(20))); //38.00
        self::assertEquals('Weekend', $tariff->getName());
        self::assertEquals(1.5, $tariff->getKmRate());
    }

    /**
     * @test
     */
    public function saturdayNightTariffShouldBeDisplayedAndCalculated(): void
    {
        //given
        $tariff = Tariff::ofTime(new \DateTimeImmutable('2021-04-17 19:30'));

        //expect
        self::assertEquals(Money::from(6000), $tariff->calculateCost(Distance::ofKm(20))); //60.00
        self::assertEquals('Weekend+', $tariff->getName());
        self::assertEquals(2.5, $tariff->getKmRate());
    }
}
