<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\CarFleet\CarType;
use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Crm\ClientDTO;
use LegacyFighter\Cabs\Ride\TransitController;
use LegacyFighter\Cabs\Ride\TransitDTO;
use LegacyFighter\Cabs\Tests\Common\FixedClock;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class TariffRecognizingIntegrationTest extends KernelTestCase
{
    private Fixtures $fixtures;
    private TransitController $transitController;
    private FixedClock $clock;

    protected function setUp(): void
    {
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
        $this->transitController = $this->getContainer()->get(TransitController::class);
        $this->clock = $this->getContainer()->get(Clock::class);
    }

    /**
     * @test
     */
    public function newYearsEveTariffShouldBeDisplayed(): void
    {
        //given
        $transitId = $this->createTransit(new \DateTimeImmutable('2021-12-31 08:30'));

        //when
        $response = json_decode($this->transitController->getTransit($transitId)->getContent(), true);

        //then
        self::assertEquals('Sylwester', $response['tariff']);
        self::assertEquals(3.5, $response['kmRate']);
    }

    /**
     * @test
     */
    public function weekendTariffShouldBeDisplayed(): void
    {
        //given
        $transitId = $this->createTransit(new \DateTimeImmutable('2021-04-17 08:30'));

        //when
        $response = json_decode($this->transitController->getTransit($transitId)->getContent(), true);

        //then
        self::assertEquals('Weekend', $response['tariff']);
        self::assertEquals(1.5, $response['kmRate']);
    }

    /**
     * @test
     */
    public function weekendPlusTariffShouldBeDisplayed(): void
    {
        //given
        $transitId = $this->createTransit(new \DateTimeImmutable('2021-04-17 22:30'));

        //when
        $response = json_decode($this->transitController->getTransit($transitId)->getContent(), true);

        //then
        self::assertEquals('Weekend+', $response['tariff']);
        self::assertEquals(2.5, $response['kmRate']);
    }

    /**
     * @test
     */
    public function standardTariffShouldBeDisplayed(): void
    {
        //given
        $transitId = $this->createTransit(new \DateTimeImmutable('2021-04-13 22:30'));

        //when
        $response = json_decode($this->transitController->getTransit($transitId)->getContent(), true);

        //then
        self::assertEquals('Standard', $response['tariff']);
        self::assertEquals(1.0, $response['kmRate']);
    }

    private function createTransit(\DateTimeImmutable $when): Uuid
    {
        $client = $this->fixtures->aClient();
        $this->clock->setDateTime($when);
        $response = $this->transitController->createTransit(TransitDTO::with(
            1, Uuid::v4(), '', '', 2.5,
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            null, ClientDTO::from($client), null, CarType::CAR_CLASS_VAN
        ));

        return Uuid::fromString(json_decode($response->getContent(), true)['requestUuid']);
    }
}
