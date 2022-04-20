<?php

namespace LegacyFighter\Cabs\Tests\Unit\Pricing;

use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Ride\RequestForTransit;
use PHPUnit\Framework\TestCase;

class CalculateTransitPriceTest extends TestCase
{
    /**
     * @test
     */
    public function calculatePriceOnRegularDay(): void
    {
        //given
        $requestForTransit = $this->transitWasOnDoneOnFriday(Distance::ofKm(20));
        //when
        $price = $requestForTransit->getEstimatedPrice();

        //then
        self::assertEquals(Money::from(2900), $price); //29.00
    }

    /**
     * @test
     */
    public function calculatePriceOnSunday(): void
    {
        //given
        $requestForTransit = $this->transitWasDoneOnSunday(Distance::ofKm(20));
        //when
        $price = $requestForTransit->getEstimatedPrice();

        //then
        self::assertEquals(Money::from(3800), $price); //39.00
    }

    /**
     * @test
     */
    public function calculatePriceOnNewYearsEve(): void
    {
        //given
        $requestForTransit = $this->transitWasDoneOnNewYearsEve(Distance::ofKm(20));

        //when
        $price = $requestForTransit->getEstimatedPrice();

        //then
        self::assertEquals(Money::from(8100), $price); //81.00
    }

    /**
     * @test
     */
    public function calculatePriceOnSaturday(): void
    {
        //given
        $requestForTransit = $this->transitWasDoneOnSaturday(Distance::ofKm(20));

        //when
        $price = $requestForTransit->getEstimatedPrice();

        //then
        self::assertEquals(Money::from(3800), $price); //38.00
    }

    /**
     * @test
     */
    public function calculatePriceOnSaturdayNight(): void
    {
        //given
        $requestForTransit = $this->transitWasDoneOnSaturdayNight(Distance::ofKm(20));

        //when
        $price = $requestForTransit->getEstimatedPrice();

        //then
        self::assertEquals(Money::from(6000), $price); //60.00
    }

    private function transitWasOnDoneOnFriday(Distance $distance): RequestForTransit
    {
        return new RequestForTransit(Tariff::ofTime(new \DateTimeImmutable('2021-04-16 08:30')), $distance);
    }

    private function transitWasDoneOnNewYearsEve(Distance $distance): RequestForTransit
    {
        return new RequestForTransit(Tariff::ofTime(new \DateTimeImmutable('2021-12-31 08:30')), $distance);
    }

    private function transitWasDoneOnSaturday(Distance $distance): RequestForTransit
    {
        return new RequestForTransit(Tariff::ofTime(new \DateTimeImmutable('2021-04-17 08:30')), $distance);
    }

    private function transitWasDoneOnSunday(Distance $distance): RequestForTransit
    {
        return new RequestForTransit(Tariff::ofTime(new \DateTimeImmutable('2021-04-18 08:30')), $distance);
    }

    private function transitWasDoneOnSaturdayNight(Distance $distance): RequestForTransit
    {
        return new RequestForTransit(Tariff::ofTime(new \DateTimeImmutable('2021-04-17 19:30')), $distance);
    }
}
