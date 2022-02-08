<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\DTO\AddressDTO;
use LegacyFighter\Cabs\Entity\CarType;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverFee;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Service\DriverSessionService;
use LegacyFighter\Cabs\Service\DriverTrackingService;
use LegacyFighter\Cabs\Service\GeocodingService;
use LegacyFighter\Cabs\Service\TransitService;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransitLifeCycleIntegrationTest extends KernelTestCase
{
    private Fixtures $fixtures;
    private TransitService $transitService;
    private GeocodingService|MockObject $geocodingService;
    private DriverSessionService $driverSessionService;
    private DriverTrackingService $driverTrackingService;

    protected function setUp(): void
    {
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
        $this->geocodingService = $this->getContainer()->get(GeocodingService::class);
        $this->transitService = $this->getContainer()->get(TransitService::class);
        $this->driverSessionService = $this->getContainer()->get(DriverSessionService::class);
        $this->driverTrackingService = $this->getContainer()->get(DriverTrackingService::class);
        $this->fixtures->anActiveCarCategory(CarType::CAR_CLASS_VAN);
    }

    /**
     * @test
     */
    public function canCreateTransit(): void
    {
        //when
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 20)
        );

        //then
        $loaded = $this->transitService->loadTransit($transit->getId());
        self::assertNull($loaded->getCarClass());
        self::assertNull($loaded->getClaimDTO());
        self::assertNotNull($loaded->getEstimatedPrice());
        self::assertNull($loaded->getPrice());
        self::assertEquals('Polska', $loaded->getFrom()->getCountry());
        self::assertEquals('Warszawa', $loaded->getFrom()->getCity());
        self::assertEquals('Młynarska', $loaded->getFrom()->getStreet());
        self::assertEquals(20, $loaded->getFrom()->getBuildingNumber());
        self::assertEquals('Polska', $loaded->getTo()->getCountry());
        self::assertEquals('Warszawa', $loaded->getTo()->getCity());
        self::assertEquals('Żytnia', $loaded->getTo()->getStreet());
        self::assertEquals(20, $loaded->getTo()->getBuildingNumber());
        self::assertEquals(Transit::STATUS_DRAFT, $transit->getStatus());
        self::assertNotNull($loaded->getTariff());
        self::assertNotEquals(0, $loaded->getKmRate());
        self::assertNotNull($loaded->getDateTime());
    }

    /**
     * @test
     */
    public function canChangeTransitDestination(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );

        //when
        $this->transitService->changeTransitAddressTo($transit->getId(), $this->fixtures->anAddressDTO(
            'Polska', 'Warszawa', 'Mazowiecka', 30
        ));

        //then
        $loaded = $this->transitService->loadTransit($transit->getId());
        self::assertEquals(30, $loaded->getTo()->getBuildingNumber());
        self::assertEquals('Mazowiecka', $loaded->getTo()->getStreet());
        self::assertNotNull($loaded->getEstimatedPrice());
        self::assertNull($loaded->getPrice());
    }

    /**
     * @test
     */
    public function cannotChangeDestinationWhenTransitIsCompleted(): void
    {
        //given
        $destination = $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $destination
        );
        //and
        $driver = $this->aNearbyDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());
        //and
        $this->transitService->acceptTransit($driver, $transit->getId());
        //and
        $this->transitService->startTransit($driver, $transit->getId());
        //and
        $this->transitService->completeTransit($driver, $transit->getId(), $destination);

        //expect
        $this->expectException(\InvalidArgumentException::class);
        $this->transitService->changeTransitAddressTo(
            $transit->getId(),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 23)
        );
    }

    /**
     * @test
     */
    public function canChangePickupPlace(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );

        //when
        $this->transitService->changeTransitAddressFrom($transit->getId(), $this->fixtures->anAddressDTO(
            'Polska', 'Warszawa', 'Puławska', 28
        ));

        //then
        $loaded = $this->transitService->loadTransit($transit->getId());
        self::assertEquals(28, $loaded->getFrom()->getBuildingNumber());
        self::assertEquals('Puławska', $loaded->getFrom()->getStreet());
    }

    /**
     * @test
     */
    public function cannotChangePickupPlaceAfterTransitIsAccepted(): void
    {
        //given
        $destination = $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $destination
        );
        //and
        $driver = $this->aNearbyDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());
        //and
        $this->transitService->acceptTransit($driver, $transit->getId());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $this->transitService->changeTransitAddressFrom($transit->getId(), $destination)
        );

        //and
        $this->transitService->startTransit($driver, $transit->getId());
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $this->transitService->changeTransitAddressFrom($transit->getId(), $destination)
        );

        //and
        $this->transitService->completeTransit($driver, $transit->getId(), $destination);
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $this->transitService->changeTransitAddressFrom($transit->getId(), $destination)
        );
    }

    /**
     * @test
     */
    public function cannotChangePickupPlaceMoreThanThreeTimes(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );
        //and
        $this->transitService->changeTransitAddressFrom($transit->getId(), $this->fixtures->anAddressDTO(
            'Polska', 'Warszawa', 'Żytnia', 26
        ));
        //and
        $this->transitService->changeTransitAddressFrom($transit->getId(), $this->fixtures->anAddressDTO(
            'Polska', 'Warszawa', 'Żytnia', 27
        ));
        //and
        $this->transitService->changeTransitAddressFrom($transit->getId(), $this->fixtures->anAddressDTO(
            'Polska', 'Warszawa', 'Żytnia', 28
        ));

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $this->transitService->changeTransitAddressFrom($transit->getId(), $this->fixtures->anAddressDTO(
                'Polska', 'Warszawa', 'Żytnia', 29
            ))
        );
    }

    /**
     * @test
     */
    public function cannotChangePickupPlaceWhenItIsFarWayFromOriginal(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $this->transitService->changeTransitAddressFrom($transit->getId(), $this->farAwayAddress())
        );
    }

    /**
     * @test
     */
    public function canCancelTransit(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );

        //when
        $this->transitService->cancelTransit($transit->getId());

        //then
        $loaded = $this->transitService->loadTransit($transit->getId());
        self::assertEquals(Transit::STATUS_CANCELLED, $loaded->getStatus());
    }

    /**
     * @test
     */
    public function cannotCancelTransitAfterItWasStarted(): void
    {
        //given
        $destination = $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $destination
        );
        //and
        $driver = $this->aNearbyDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());
        //and
        $this->transitService->acceptTransit($driver, $transit->getId());

        //and
        $this->transitService->startTransit($driver, $transit->getId());
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $this->transitService->cancelTransit($transit->getId())
        );

        //and
        $this->transitService->completeTransit($driver, $transit->getId(), $destination);
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $this->transitService->cancelTransit($transit->getId())
        );
    }

    /**
     * @test
     */
    public function canPublishTransit(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );
        //and
        $this->aNearbyDriver('WU1212');

        //when
        $this->transitService->publishTransit($transit->getId());

        //then
        $loaded = $this->transitService->loadTransit($transit->getId());
        self::assertEquals(Transit::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT, $loaded->getStatus());
        self::assertNotNull($loaded->getPublished());
    }

    /**
     * @test
     */
    public function canAcceptTransit(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );
        //and
        $driver = $this->aNearbyDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());

        //when
        $this->transitService->acceptTransit($driver, $transit->getId());

        //then
        $loaded = $this->transitService->loadTransit($transit->getId());
        self::assertEquals(Transit::STATUS_TRANSIT_TO_PASSENGER, $loaded->getStatus());
        self::assertNotNull($loaded->getAcceptedAt());
    }

    /**
     * @test
     */
    public function onlyOneDriverCanAcceptTransit(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );
        //and
        $driver = $this->aNearbyDriver('WU1212');
        //and
        $secondDriver = $this->aNearbyDriver('DW MARIO');
        //and
        $this->transitService->publishTransit($transit->getId());
        //and
        $this->transitService->acceptTransit($driver, $transit->getId());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\RuntimeException::class,
            fn() => $this->transitService->acceptTransit($secondDriver, $transit->getId())
        );
    }

    /**
     * @test
     */
    public function transitCannotByAcceptedByDriverWhoAlreadyRejected(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );
        //and
        $driver = $this->aNearbyDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());
        //and
        $this->transitService->rejectTransit($driver, $transit->getId());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\RuntimeException::class,
            fn() => $this->transitService->acceptTransit($driver, $transit->getId())
        );
    }

    /**
     * @test
     */
    public function transitCannotByAcceptedByDriverWhoHasNotSeenProposal(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );
        //and
        $farAwayDriver = $this->aFarAwayDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\RuntimeException::class,
            fn() => $this->transitService->acceptTransit($farAwayDriver, $transit->getId())
        );
    }

    /**
     * @test
     */
    public function canStartTransit(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );
        //and
        $driver = $this->aNearbyDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());
        //and
        $this->transitService->acceptTransit($driver, $transit->getId());
        //when
        $this->transitService->startTransit($driver, $transit->getId());

        //then
        $loaded = $this->transitService->loadTransit($transit->getId());
        self::assertEquals(Transit::STATUS_IN_TRANSIT, $loaded->getStatus());
        self::assertNotNull($loaded->getStarted());
    }

    /**
     * @test
     */
    public function cannotStartNotAcceptedTransit(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );
        //and
        $farAwayDriver = $this->aFarAwayDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $this->transitService->startTransit($farAwayDriver, $transit->getId())
        );
    }

    /**
     * @test
     */
    public function canCompleteTransit(): void
    {
        //given
        $destination = $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $destination
        );
        //and
        $driver = $this->aNearbyDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());
        //and
        $this->transitService->acceptTransit($driver, $transit->getId());
        //and
        $this->transitService->startTransit($driver, $transit->getId());

        //when
        $this->transitService->completeTransit($driver, $transit->getId(), $destination);

        //then
        $loaded = $this->transitService->loadTransit($transit->getId());
        self::assertEquals(Transit::STATUS_COMPLETED, $loaded->getStatus());
        self::assertNotNull($loaded->getPrice());
        self::assertNotNull($loaded->getDriverFee());
        self::assertNotNull($loaded->getCompletedAt());
    }

    /**
     * @test
     */
    public function cannotCompleteNotStartedTransit(): void
    {
        //given
        $destination = $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $destination
        );
        //and
        $driver = $this->aNearbyDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());
        //and
        $this->transitService->acceptTransit($driver, $transit->getId());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\RuntimeException::class,
            fn() => $this->transitService->completeTransit($driver, $transit->getId(), $destination)
        );
    }

    /**
     * @test
     */
    public function canRejectTransit(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Młynarska', 20),
            $this->fixtures->anAddressDTO('Polska', 'Warszawa', 'Żytnia', 25)
        );
        //and
        $driver = $this->aNearbyDriver('WU1212');
        //and
        $this->transitService->publishTransit($transit->getId());

        //when
        $this->transitService->rejectTransit($driver, $transit->getId());

        //then
        $loaded = $this->transitService->loadTransit($transit->getId());
        self::assertEquals(Transit::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT, $loaded->getStatus());
        self::assertNull($loaded->getAcceptedAt());
    }

    private function farAwayAddress(): AddressDTO
    {
        $addressDTO = $this->fixtures->anAddressDTO('Dania', 'Kopenhaga', 'Mylve', 2);
        $this->geocodingService->setReturnValues([[1.0, 1.0], [1000.0, 1000.0]]);
        return $addressDTO;
    }

    private function aNearbyDriver(string $plateNumber): int
    {
        $driver = $this->fixtures->aDriver();
        $this->fixtures->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);
        $this->driverSessionService->logIn($driver->getId(), $plateNumber, CarType::CAR_CLASS_VAN, 'BRAND');
        $this->driverTrackingService->registerPosition($driver->getId(), 1, 1);
        return $driver->getId();
    }

    private function aFarAwayDriver(string $plateNumber): int
    {
        $driver = $this->fixtures->aDriver();
        $this->fixtures->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);
        $this->driverSessionService->logIn($driver->getId(), $plateNumber, CarType::CAR_CLASS_VAN, 'BRAND');
        $this->driverTrackingService->registerPosition($driver->getId(), 1000, 1000);
        return $driver->getId();
    }

    private function requestTransitFromTo(AddressDTO $pickup, AddressDTO $destination): Transit
    {
        return $this->transitService->createTransit($this->fixtures->aTransitDTO($pickup, $destination));
    }

    private static function assertThatExceptionOfTypeIsThrownBy(string $exception, callable $callable): void
    {
        try {
            $callable();
        } catch (\Throwable $throwable) {
        }

        self::assertInstanceOf($exception, $throwable);
    }
}
