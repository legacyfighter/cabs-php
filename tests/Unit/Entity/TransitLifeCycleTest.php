<?php

namespace LegacyFighter\Cabs\Tests\Unit\Entity;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Tests\Common\PrivateProperty;
use PHPUnit\Framework\TestCase;

class TransitLifeCycleTest extends TestCase
{
    /**
     * @test
     */
    public function canCreateTransit(): void
    {
        //when
        $transit = $this->requestTransit();

        //then
        self::assertNull($transit->getPrice());
        self::assertEquals(Transit::STATUS_DRAFT, $transit->getStatus());
        self::assertNotNull($transit->getTariff());
        self::assertNotEquals(0, $transit->getTariff()->getKmRate());
    }

    /**
     * @test
     */
    public function canChangeTransitDestination(): void
    {
        //given
        $transit = $this->requestTransit();

        //when
        $transit->changeDestinationTo(
            new Address('Polska', 'Warszawa', 'Mazowiecka', 30), Distance::ofKm(20.0));

        //then
        self::assertNotNull($transit->getEstimatedPrice());
        self::assertNull($transit->getPrice());
    }

    /**
     * @test
     */
    public function cannotChangeDestinationWhenTransitIsCompleted(): void
    {
        //given
        $destination = new Address('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $driver = new Driver();
        //and
        $transit = $this->requestTransit();
        //and
        $transit->publishAt(new \DateTimeImmutable());
        //and
        $transit->proposeTo($driver);
        //and
        $transit->acceptBy($driver, new \DateTimeImmutable());
        //and
        $transit->start(new \DateTimeImmutable());
        //and
        $transit->completeAt(new \DateTimeImmutable(), $destination, Distance::ofKm(20.0));

        //expect
        $this->expectException(\InvalidArgumentException::class);
        $transit->changeDestinationTo(
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
            Distance::ofKm(20.0)
        );
    }

    /**
     * @test
     */
    public function canChangePickupPlace(): void
    {
        //given
        $transit = $this->requestTransit();

        //when
        $transit->changePickupTo(
            new Address('Polska', 'Warszawa', 'Puławska', 28), Distance::ofKm(20.0), 0.2
        );

        //then
        self::assertTrue(true);
    }

    /**
     * @test
     */
    public function cannotChangePickupPlaceAfterTransitIsAccepted(): void
    {
        //given
        $destination = new Address('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $driver = new Driver();
        //and
        $transit = $this->requestTransit();
        //and
        $transit->publishAt(new \DateTimeImmutable());
        //and
        $transit->proposeTo($driver);
        //and
        $transit->acceptBy($driver, new \DateTimeImmutable());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $transit->changePickupTo(
                new Address('Polska', 'Warszawa', 'Puławska', 28), Distance::ofKm(20.0), 0.2
            )
        );

        //and
        $transit->start(new \DateTimeImmutable());
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $transit->changePickupTo(
                new Address('Polska', 'Warszawa', 'Puławska', 28), Distance::ofKm(20.0), 0.2
            )
        );

        //and
        $transit->completeAt(new \DateTimeImmutable(), $destination, Distance::ofKm(20.0));
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $transit->changePickupTo(
                new Address('Polska', 'Warszawa', 'Puławska', 28), Distance::ofKm(20.0), 0.2
            )
        );
    }

    /**
     * @test
     */
    public function cannotChangePickupPlaceMoreThanThreeTimes(): void
    {
        //given
        $transit = $this->requestTransit();
        //and
        $transit->changePickupTo(
            new Address('Polska', 'Warszawa', 'Żytnia', 26), Distance::ofKm(20.0), 0.2
        );
        //and
        $transit->changePickupTo(
            new Address('Polska', 'Warszawa', 'Żytnia', 27), Distance::ofKm(20.0), 0.2
        );
        //and
        $transit->changePickupTo(
            new Address('Polska', 'Warszawa', 'Żytnia', 28), Distance::ofKm(20.0), 0.2
        );

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $transit->changePickupTo(
                new Address('Polska', 'Warszawa', 'Żytnia', 29), Distance::ofKm(20.0), 0.2
            )
        );
    }

    /**
     * @test
     */
    public function cannotChangePickupPlaceWhenItIsFarWayFromOriginal(): void
    {
        //given
        $transit = $this->requestTransit();

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class, fn() =>
            $transit->changePickupTo(
                new Address('Polska', 'Warszawa', 'Żytnia', 28), Distance::ofKm(20.0), 50
            )
        );
    }

    /**
     * @test
     */
    public function canCancelTransit(): void
    {
        //given
        $transit = $this->requestTransit();

        //when
        $transit->cancel();

        //then
        self::assertEquals(Transit::STATUS_CANCELLED, $transit->getStatus());
    }

    /**
     * @test
     */
    public function cannotCancelTransitAfterItWasStarted(): void
    {
        //given
        $destination = new Address('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $driver = new Driver();
        //and
        $transit = $this->requestTransit();
        //and
        $transit->publishAt(new \DateTimeImmutable());
        //and
        $transit->proposeTo($driver);
        //and
        $transit->acceptBy($driver, new \DateTimeImmutable());


        //and
        $transit->start(new \DateTimeImmutable());
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $transit->cancel()
        );

        //and
        $transit->completeAt(new \DateTimeImmutable(), $destination, Distance::ofKm(20));
        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $transit->cancel()
        );
    }

    /**
     * @test
     */
    public function canPublishTransit(): void
    {
        //given
        $transit = $this->requestTransit();

        //when
        $transit->publishAt(new \DateTimeImmutable());

        //then
        self::assertEquals(Transit::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT, $transit->getStatus());
        self::assertNotNull($transit->getPublished());
    }

    /**
     * @test
     */
    public function canAcceptTransit(): void
    {
        //given
        $transit = $this->requestTransit();
        //and
        $driver = new Driver();
        //and
        $transit->publishAt(new \DateTimeImmutable());
        //and
        $transit->proposeTo($driver);

        //when
        $transit->acceptBy($driver, new \DateTimeImmutable());

        //then
        self::assertEquals(Transit::STATUS_TRANSIT_TO_PASSENGER, $transit->getStatus());
    }

    /**
     * @test
     */
    public function onlyOneDriverCanAcceptTransit(): void
    {
        //given
        $transit = $this->requestTransit();
        //and
        $driver = new Driver();
        //and
        $secondDriver = new Driver();
        //and
        $transit->publishAt(new \DateTimeImmutable());
        //and
        $transit->proposeTo($driver);

        //when
        $transit->acceptBy($driver, new \DateTimeImmutable());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\RuntimeException::class,
            fn() => $transit->acceptBy($secondDriver, new \DateTimeImmutable())
        );
    }

    /**
     * @test
     */
    public function transitCannotByAcceptedByDriverWhoAlreadyRejected(): void
    {
        //given
        $transit = $this->requestTransit();
        //and
        $driver = new Driver();
        //and
        $transit->publishAt(new \DateTimeImmutable());
        //and
        $transit->rejectBy($driver);

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\RuntimeException::class,
            fn() => $transit->acceptBy($driver, new \DateTimeImmutable())
        );
    }

    /**
     * @test
     */
    public function transitCannotByAcceptedByDriverWhoHasNotSeenProposal(): void
    {
        //given
        $transit = $this->requestTransit();
        //and
        $driver = new Driver();
        //and
        $transit->publishAt(new \DateTimeImmutable());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\RuntimeException::class,
            fn() => $transit->acceptBy($driver, new \DateTimeImmutable())
        );
    }

    /**
     * @test
     */
    public function canStartTransit(): void
    {
        //given
        $transit = $this->requestTransit();
        //and
        $driver = new Driver();
        //and
        $transit->publishAt(new \DateTimeImmutable());
        //and
        $transit->proposeTo($driver);
        //and
        $transit->acceptBy($driver, new \DateTimeImmutable());
        //when
        $transit->start(new \DateTimeImmutable());

        //then
        self::assertEquals(Transit::STATUS_IN_TRANSIT, $transit->getStatus());
    }

    /**
     * @test
     */
    public function cannotStartNotAcceptedTransit(): void
    {
        //given
        $transit = $this->requestTransit();
        //and
        $transit->publishAt(new \DateTimeImmutable());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\InvalidArgumentException::class,
            fn() => $transit->start(new \DateTimeImmutable())
        );
    }

    /**
     * @test
     */
    public function canCompleteTransit(): void
    {
        //given
        $destination = new Address('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $transit = $this->requestTransit();
        //and
        $driver = new Driver();
        //and
        $transit->publishAt(new \DateTimeImmutable());
        //and
        $transit->proposeTo($driver);
        //and
        $transit->acceptBy($driver, new \DateTimeImmutable());
        //when
        $transit->start(new \DateTimeImmutable());

        //when
        $transit->completeAt(new \DateTimeImmutable(), $destination, Distance::ofKm(20.0));

        //then
        self::assertEquals(Transit::STATUS_COMPLETED, $transit->getStatus());
        self::assertNotNull($transit->getPrice());
        self::assertNotNull($transit->getTariff());
    }

    /**
     * @test
     */
    public function cannotCompleteNotStartedTransit(): void
    {
        //given
        $destination = new Address('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $transit = $this->requestTransit();
        //and
        $driver = new Driver();
        //and
        $transit->publishAt(new \DateTimeImmutable());
        //and
        $transit->proposeTo($driver);
        //and
        $transit->acceptBy($driver, new \DateTimeImmutable());

        //expect
        self::assertThatExceptionOfTypeIsThrownBy(\RuntimeException::class,
            fn() => $transit->completeAt(new \DateTimeImmutable(), $destination, Distance::ofKm(20.0))
        );
    }

    /**
     * @test
     */
    public function canRejectTransit(): void
    {
        //given
        $transit = $this->requestTransit();
        //and
        $driver = new Driver();
        //and
        $transit->publishAt(new \DateTimeImmutable());

        //when
        $transit->rejectBy($driver);

        //then
        self::assertEquals(Transit::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT, $transit->getStatus());
    }

    private function requestTransit(): Transit
    {
        $transit = new Transit(new Client(), new \DateTimeImmutable(), Distance::zero());
        PrivateProperty::setId(1, $transit);
        return $transit;
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
