<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Service\DriverTrackingService;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DriverTrackingServiceIntegrationTest extends KernelTestCase
{
    private DriverTrackingService $driverTrackingService;
    private Fixtures $fixtures;
    private Clock $clock;

    protected function setUp(): void
    {
        $this->driverTrackingService = $this->getContainer()->get(DriverTrackingService::class);
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
        $this->clock = $this->getContainer()->get(Clock::class);
    }

    /**
     * @test
     */
    public function canCalculateTravelledDistanceFromShortTransit(): void
    {
        //given
        $driver = $this->fixtures->aDriver();
        //and
        $this->itIsNoon();
        //and
        $this->driverTrackingService->registerPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noon());
        $this->driverTrackingService->registerPosition($driver->getId(), 53.31861111111111, -1.6997222222222223, $this->noon());
        $this->driverTrackingService->registerPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noon());

        //when
        $distance = $this->driverTrackingService->calculateTravelledDistance($driver->getId(), $this->noon(), $this->noonFive());

        //then
        self::assertSame('4.009km', $distance->printIn('km'));
    }

    private function itIsNoon(): void
    {
        $this->clock->setDateTime($this->noon());
    }

    private function noon(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('1989-12-12 12:12');
    }

    private function noonFive(): \DateTimeImmutable
    {
        return $this->noon()->modify('+5 minutes');
    }

}
