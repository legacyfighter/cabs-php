<?php

namespace LegacyFighter\Cabs\Tests\Unit\Ride;

use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Ride\Details\Status;
use LegacyFighter\Cabs\Ride\Transit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TransitTest extends TestCase
{
    /**
     * @test
     */
    public function canChangeTransitDestination(): void
    {
        //given
        $transit = $this->transit();

        //expect
        $transit->changeDestination(Distance::ofKm(20));

        //then
        self::assertEquals(Distance::ofKm(20), $transit->getDistance());
    }

    /**
     * @test
     */
    public function cannotChangeDestinationWhenTransitIsCompleted(): void
    {
        //given
        $transit = $this->transit();
        //and
        $transit->completeAt(Distance::ofKm(20));

        //then
        self::expectException(\RuntimeException::class);

        //when
        $transit->changeDestination(Distance::ofKm(20));
    }

    /**
     * @test
     */
    public function canCompleteTransit(): void
    {
        //given
        $transit = $this->transit();

        //expect
        $transit->completeAt(Distance::ofKm(20));

        //then
        self::assertEquals(Status::COMPLETED, $transit->getStatus());
    }

    private function transit(): Transit
    {
        return new Transit(Tariff::ofTime(new \DateTimeImmutable()), Uuid::v4());
    }
}
