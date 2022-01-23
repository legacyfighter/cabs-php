<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Tests\Common\Fixtures;
use LegacyFighter\Cabs\Ui\TransitController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TariffRecognizingIntegrationTest extends KernelTestCase
{
    private Fixtures $fixtures;
    private TransitController $transitController;

    protected function setUp(): void
    {
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
        $this->transitController = $this->getContainer()->get(TransitController::class);
    }

    /**
     * @test
     */
    public function newYearsEveTariffShouldBeDisplayed(): void
    {
        //given
        $transit = $this->fixtures->aCompletedTransitAt(60, new \DateTimeImmutable('2021-12-31 08:30'));

        //when
        $response = json_decode($this->transitController->getTransit($transit->getId())->getContent(), true);

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
        $transit = $this->fixtures->aCompletedTransitAt(60, new \DateTimeImmutable('2021-04-17 08:30'));

        //when
        $response = json_decode($this->transitController->getTransit($transit->getId())->getContent(), true);

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
        $transit = $this->fixtures->aCompletedTransitAt(60, new \DateTimeImmutable('2021-04-17 22:30'));

        //when
        $response = json_decode($this->transitController->getTransit($transit->getId())->getContent(), true);

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
        $transit = $this->fixtures->aCompletedTransitAt(60, new \DateTimeImmutable('2021-04-13 22:30'));

        //when
        $response = json_decode($this->transitController->getTransit($transit->getId())->getContent(), true);

        //then
        self::assertEquals('Standard', $response['tariff']);
        self::assertEquals(1.0, $response['kmRate']);
    }
}
