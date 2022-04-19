<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\DriverFleet\DriverReport\TravelledDistance\TravelledDistanceService;
use LegacyFighter\Cabs\Tests\Common\FixedClock;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CalculateDriverTravelledDistanceIntegrationTest extends KernelTestCase
{
    private TravelledDistanceService $travelledDistanceService;
    private Fixtures $fixtures;
    private FixedClock $clock;

    protected function setUp(): void
    {
        $this->travelledDistanceService = $this->getContainer()->get(TravelledDistanceService::class);
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
        $this->clock = $this->getContainer()->get(Clock::class);
    }

    /**
     * @test
     */
    public function distanceIsZeroWhenZeroPositions(): void
    {
        //given
        $driver = $this->fixtures->aDriver();

        //when
        $distance = $this->travelledDistanceService->calculateDistance($driver->getId(), $this->noon(), $this->noonFive());

        //then
        self::assertSame('0km', $distance->printIn('km'));
    }

    /**
     * @test
     */
    public function travelledDistanceWithoutMultiplePositionsIzZero(): void
    {
        //given
        $driver = $this->fixtures->aDriver();
        //and
        $this->itIsNoon();
        //and
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noon());

        //when
        $distance = $this->travelledDistanceService->calculateDistance($driver->getId(), $this->noon(), $this->noonFive());

        //then
        self::assertSame('0km', $distance->printIn('km'));
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
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noon());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.31861111111111, -1.6997222222222223, $this->noon());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noon());

        //when
        $distance = $this->travelledDistanceService->calculateDistance($driver->getId(), $this->noon(), $this->noonFive());

        //then
        self::assertSame('4.009km', $distance->printIn('km'));
    }

    /**
     * @test
     */
    public function canCalculateTravelledDistanceWithBreakWithin(): void
    {
        //given
        $driver = $this->fixtures->aDriver();
        //and
        $this->itIsNoon();
        //and
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noon());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.31861111111111, -1.6997222222222223, $this->noon());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noon());
        //and
        $this->itIsNoonFive();
        //and
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noonFive());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.31861111111111, -1.6997222222222223, $this->noonFive());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noonFive());

        //when
        $distance = $this->travelledDistanceService->calculateDistance($driver->getId(), $this->noon(), $this->noonFive());

        //then
        self::assertSame('8.017km', $distance->printIn('km'));
    }

    /**
     * @test
     */
    public function canCalculateTravelledDistanceWithMultipleBreaks(): void
    {
        //given
        $driver = $this->fixtures->aDriver();
        //and
        $this->itIsNoon();
        //and
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noon());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.31861111111111, -1.6997222222222223, $this->noon());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noon());
        //and
        $this->itIsNoonFive();
        //and
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noonFive());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.31861111111111, -1.6997222222222223, $this->noonFive());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noonFive());
        //and
        $this->itIsNoonTen();
        //and
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noonTen());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.31861111111111, -1.6997222222222223, $this->noonTen());
        $this->travelledDistanceService->addPosition($driver->getId(), 53.32055555555556, -1.7297222222222221, $this->noonTen());

        //when
        $distance = $this->travelledDistanceService->calculateDistance($driver->getId(), $this->noon(), $this->noonTen());

        //then
        self::assertSame('12.026km', $distance->printIn('km'));
    }

    private function itIsNoon(): void
    {
        $this->clock->setDateTime($this->noon());
    }

    private function itIsNoonFive(): void
    {
        $this->clock->setDateTime($this->noonFive());
    }

    private function itIsNoonTen(): void
    {
        $this->clock->setDateTime($this->noonTen());
    }

    private function noon(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('1989-12-12 12:12');
    }

    private function noonFive(): \DateTimeImmutable
    {
        return $this->noon()->modify('+5 minutes');
    }

    private function noonTen(): \DateTimeImmutable
    {
        return $this->noon()->modify('+10 minutes');
    }
}
