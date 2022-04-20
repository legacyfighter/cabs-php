<?php

namespace LegacyFighter\Cabs\Tests\Unit\Ride;

use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Ride\RequestForTransit;
use PHPUnit\Framework\TestCase;

class RequestForTransitTest extends TestCase
{
    /**
     * @test
     */
    public function canCreateRequestForTransit(): void
    {
        //when
        $requestForTransit = new RequestForTransit(Tariff::ofTime(new \DateTimeImmutable()), Distance::zero());

        //expect
        self::assertNotEmpty($requestForTransit->getTariff()->getName());
        self::assertNotEquals(0,$requestForTransit->getTariff()->getKmRate());
    }
}
