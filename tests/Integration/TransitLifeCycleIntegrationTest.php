<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\CarFleet\CarType;
use LegacyFighter\Cabs\DriverFleet\DriverFee;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\GeocodingService;
use LegacyFighter\Cabs\Ride\Details\Status;
use LegacyFighter\Cabs\Ride\Transit;
use LegacyFighter\Cabs\Ride\TransitDTO;
use LegacyFighter\Cabs\Ride\RideService;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use LegacyFighter\Cabs\Tests\Double\FakeGeocodingService;
use LegacyFighter\Cabs\Tracking\DriverSessionService;
use LegacyFighter\Cabs\Tracking\DriverTrackingService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransitLifeCycleIntegrationTest extends KernelTestCase
{
    private Fixtures $fixtures;
    private RideService $rideService;
    private FakeGeocodingService $geocodingService;
    private DriverSessionService $driverSessionService;
    private DriverTrackingService $driverTrackingService;

    protected function setUp(): void
    {
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
        $this->geocodingService = $this->getContainer()->get(GeocodingService::class);
        $this->rideService = $this->getContainer()->get(RideService::class);
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
        $loaded = $this->rideService->loadTransit($transit->getRequestUuid());
        self::assertNotNull($loaded->getCarClass());
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
        self::assertEquals(Status::DRAFT, $transit->getStatus());
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
        $this->rideService->changeTransitAddressTo($transit->getRequestUuid(), $this->fixtures->anAddressDTO(
            'Polska', 'Warszawa', 'Mazowiecka', 30
        ));

        //then
        $loaded = $this->rideService->loadTransit($transit->getRequestUuid());
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
        $this->rideService->publishTransit($transit->getRequestUuid());
        //and
        $this->rideService->acceptTransit($driver, $transit->getRequestUuid());
        //and
        $this->rideService->startTransit($driver, $transit->getRequestUuid());
        //and
        $this->rideService->completeTransit($driver, $transit->getRequestUuid(), $destination);

        //expect
        $this->expectException(\RuntimeException::class);
        $this->rideService->changeTransitAddressTo(
            $transit->getRequestUuid(),
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
        //and
        $this->rideService->publishTransit($transit->getRequestUuid());

        //when
        $this->rideService->changeTransitAddressFrom($transit->getRequestUuid(), $this->fixtures->anAddressDTO(
            'Polska', 'Warszawa', 'Puławska', 28
        ));

        //then
        $loaded = $this->rideService->loadTransit($transit->getRequestUuid());
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
        $this->rideService->publishTransit($transit->getRequestUuid());
        //and
        $this->rideService->acceptTransit($driver, $transit->getRequestUuid());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $this->rideService->changeTransitAddressFrom($transit->getRequestUuid(), $destination)
        );

        //and
        $this->rideService->startTransit($driver, $transit->getRequestUuid());
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $this->rideService->changeTransitAddressFrom($transit->getRequestUuid(), $destination)
        );

        //and
        $this->rideService->completeTransit($driver, $transit->getRequestUuid(), $destination);
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $this->rideService->changeTransitAddressFrom($transit->getRequestUuid(), $destination)
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
        $this->rideService->publishTransit($transit->getRequestUuid());
        //and
        $this->rideService->changeTransitAddressFrom($transit->getRequestUuid(), $this->fixtures->anAddressDTO(
            'Polska', 'Warszawa', 'Żytnia', 26
        ));
        //and
        $this->rideService->changeTransitAddressFrom($transit->getRequestUuid(), $this->fixtures->anAddressDTO(
            'Polska', 'Warszawa', 'Żytnia', 27
        ));
        //and
        $this->rideService->changeTransitAddressFrom($transit->getRequestUuid(), $this->fixtures->anAddressDTO(
            'Polska', 'Warszawa', 'Żytnia', 28
        ));

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $this->rideService->changeTransitAddressFrom($transit->getRequestUuid(), $this->fixtures->anAddressDTO(
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
            $this->rideService->changeTransitAddressFrom($transit->getRequestUuid(), $this->farAwayAddress())
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
        $this->rideService->cancelTransit($transit->getRequestUuid());

        //then
        $loaded = $this->rideService->loadTransit($transit->getRequestUuid());
        self::assertEquals(Status::CANCELLED, $loaded->getStatus());
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
        $this->rideService->publishTransit($transit->getRequestUuid());
        //and
        $this->rideService->acceptTransit($driver, $transit->getRequestUuid());

        //and
        $this->rideService->startTransit($driver, $transit->getRequestUuid());
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $this->rideService->cancelTransit($transit->getRequestUuid())
        );

        //and
        $this->rideService->completeTransit($driver, $transit->getRequestUuid(), $destination);
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $this->rideService->cancelTransit($transit->getRequestUuid())
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
        $this->rideService->publishTransit($transit->getRequestUuid());

        //then
        $loaded = $this->rideService->loadTransit($transit->getRequestUuid());
        self::assertEquals(Status::WAITING_FOR_DRIVER_ASSIGNMENT, $loaded->getStatus());
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
        $this->rideService->publishTransit($transit->getRequestUuid());

        //when
        $this->rideService->acceptTransit($driver, $transit->getRequestUuid());

        //then
        $loaded = $this->rideService->loadTransit($transit->getRequestUuid());
        self::assertEquals(Status::TRANSIT_TO_PASSENGER, $loaded->getStatus());
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
        $this->rideService->publishTransit($transit->getRequestUuid());
        //and
        $this->rideService->acceptTransit($driver, $transit->getRequestUuid());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $this->rideService->acceptTransit($secondDriver, $transit->getRequestUuid())
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
        $this->rideService->publishTransit($transit->getRequestUuid());
        //and
        $this->rideService->rejectTransit($driver, $transit->getRequestUuid());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\RuntimeException::class,
            fn() => $this->rideService->acceptTransit($driver, $transit->getRequestUuid())
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
        $this->rideService->publishTransit($transit->getRequestUuid());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\RuntimeException::class,
            fn() => $this->rideService->acceptTransit($farAwayDriver, $transit->getRequestUuid())
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
        $this->rideService->publishTransit($transit->getRequestUuid());
        //and
        $this->rideService->acceptTransit($driver, $transit->getRequestUuid());
        //when
        $this->rideService->startTransit($driver, $transit->getRequestUuid());

        //then
        $loaded = $this->rideService->loadTransit($transit->getRequestUuid());
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
        $this->rideService->publishTransit($transit->getRequestUuid());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $this->rideService->startTransit($farAwayDriver, $transit->getRequestUuid())
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
        $this->rideService->publishTransit($transit->getRequestUuid());
        //and
        $this->rideService->acceptTransit($driver, $transit->getRequestUuid());
        //and
        $this->rideService->startTransit($driver, $transit->getRequestUuid());

        //when
        $this->rideService->completeTransit($driver, $transit->getRequestUuid(), $destination);

        //then
        $loaded = $this->rideService->loadTransit($transit->getRequestUuid());
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
        $this->rideService->publishTransit($transit->getRequestUuid());
        //and
        $this->rideService->acceptTransit($driver, $transit->getRequestUuid());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $this->rideService->completeTransit($driver, $transit->getRequestUuid(), $destination)
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
        $this->rideService->publishTransit($transit->getRequestUuid());

        //when
        $this->rideService->rejectTransit($driver, $transit->getRequestUuid());

        //then
        $loaded = $this->rideService->loadTransit($transit->getRequestUuid());
        self::assertEquals(Status::WAITING_FOR_DRIVER_ASSIGNMENT, $loaded->getStatus());
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
        $this->driverTrackingService->registerPosition($driver->getId(), 1, 1, new \DateTimeImmutable());
        return $driver->getId();
    }

    private function aFarAwayDriver(string $plateNumber): int
    {
        $driver = $this->fixtures->aDriver();
        $this->fixtures->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);
        $this->driverSessionService->logIn($driver->getId(), $plateNumber, CarType::CAR_CLASS_VAN, 'BRAND');
        $this->driverTrackingService->registerPosition($driver->getId(), 1000, 1000, new \DateTimeImmutable());
        return $driver->getId();
    }

    private function requestTransitFromTo(AddressDTO $pickup, AddressDTO $destination): TransitDTO
    {
        return $this->rideService->createTransit($this->fixtures->aTransitDTO($pickup, $destination));
    }

    private static function assertThatExceptionOfTypeIsThrownBy(string $exception, callable $callable): void
    {
        $throwable = null;
        try {
            $callable();
        } catch (\Throwable $throwable) {
        }

        self::assertInstanceOf($exception, $throwable);
    }
}
