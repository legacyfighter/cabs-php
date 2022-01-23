<?php

namespace LegacyFighter\Cabs\Tests\Unit\Entity;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Tests\Common\PrivateProperty;
use PHPUnit\Framework\TestCase;

class CalculateTransitPriceTest extends TestCase
{
    /**
     * @test
     */
    public function cannotCalculatePriceWhenTransitIsCancelled(): void
    {
        //given
        $transit = $this->transit(Transit::STATUS_CANCELLED, 20);

        //expect
        $this->expectException(\RuntimeException::class);
        $transit->calculateFinalCosts();
    }

    /**
     * @test
     */
    public function cannotEstimatePriceWhenTransitIsCompleted(): void
    {
        //given
        $transit = $this->transit(Transit::STATUS_COMPLETED, 20);

        //expect
        $this->expectException(\RuntimeException::class);
        $transit->estimateCost();
    }

    /**
     * @test
     */
    public function calculatePriceOnRegularDay(): void
    {
        //given
        $transit = $this->transit(Transit::STATUS_COMPLETED, 20);

        //friday
        $this->transitWasOnDoneOnFriday($transit);
        //when
        $price = $transit->calculateFinalCosts();

        //then
        self::assertEquals(Money::from(2900), $price); //29.00
    }

    /**
     * @test
     */
    public function estimatePriceOnRegularDay(): void
    {
        //given
        $transit = $this->transit(Transit::STATUS_DRAFT, 20);

        //friday
        $this->transitWasOnDoneOnFriday($transit);
        //when
        $price = $transit->estimateCost();

        //then
        self::assertEquals(Money::from(2900), $price); //29.00
    }

    private function transit(string $status, float $km): Transit
    {
        $transit = new Transit();
        PrivateProperty::setId(1, $transit);
        $transit->setDateTime(new \DateTimeImmutable());
        $transit->setStatus(Transit::STATUS_DRAFT);
        $transit->setKm(Distance::ofKm($km));
        $transit->setStatus($status);
        return $transit;
    }

    private function transitWasOnDoneOnFriday(Transit $transit): void
    {
        $transit->setDateTime(new \DateTimeImmutable('2021-04-16 08:30'));
    }
}
