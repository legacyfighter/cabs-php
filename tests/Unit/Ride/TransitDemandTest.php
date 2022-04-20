<?php

namespace LegacyFighter\Cabs\Tests\Unit\Ride;

use LegacyFighter\Cabs\Ride\TransitDemand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TransitDemandTest extends TestCase
{
    /**
     * @test
     */
    public function canChangePickupPlace(): void
    {
        //given
        $transitDemand = $this->transitDemand();

        //expect
        $transitDemand->changePickupTo(0.2);
        self::assertEquals(TransitDemand::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT, $transitDemand->getStatus());
    }

    /**
     * @test
     */
    public function cannotChangePickupPlaceAfterTransitIsAccepted(): void
    {
        //given
        $transitDemand = $this->transitDemand();
        //and
        $transitDemand->accept();

        //expect
        $this->expectException(\InvalidArgumentException::class);

        //when
        $transitDemand->changePickupTo(0.1);
    }

    /**
     * @test
     */
    public function cannotChangePickupPlaceMoreThanThreeTimes(): void
    {
        //given
        $transitDemand = $this->transitDemand();
        //and
        $transitDemand->changePickupTo(0.2);
        //and
        $transitDemand->changePickupTo(0.2);
        //and
        $transitDemand->changePickupTo(0.2);

        //expect
        $this->expectException(\InvalidArgumentException::class);

        //when
        $transitDemand->changePickupTo(0.1);
    }

    /**
     * @test
     */
    public function cannotChangePickupPlaceWhenItIsFarWayFromOriginal(): void
    {
        //given
        $transitDemand = $this->transitDemand();

        //expect
        $this->expectException(\InvalidArgumentException::class);

        //then
        $transitDemand->changePickupTo(50);
    }

    /**
     * @test
     */
    public function canCancelDemand(): void
    {
        //given
        $transitDemand = $this->transitDemand();

        //then
        self::assertEquals(TransitDemand::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT, $transitDemand->getStatus());
    }

    /**
     * @test
     */
    public function canPublishDemand(): void
    {
        //given
        $transitDemand = $this->transitDemand();

        //when
        $transitDemand->cancel();

        //then
        self::assertEquals(TransitDemand::STATUS_CANCELLED, $transitDemand->getStatus());
    }

    private function transitDemand(): TransitDemand
    {
        return new TransitDemand(Uuid::v4());
    }
}
