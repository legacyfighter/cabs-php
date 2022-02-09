<?php

namespace LegacyFighter\Cabs\Tests\Unit\Entity;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\CarType;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Driver;
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );

        //then
        self::assertNull($transit->getPrice());
        self::assertEquals('Polska', $transit->getFrom()->getCountry());
        self::assertEquals('Warszawa', $transit->getFrom()->getCity());
        self::assertEquals('Młynarska', $transit->getFrom()->getStreet());
        self::assertEquals(20, $transit->getFrom()->getBuildingNumber());
        self::assertEquals('Polska', $transit->getTo()->getCountry());
        self::assertEquals('Warszawa', $transit->getTo()->getCity());
        self::assertEquals('Żytnia', $transit->getTo()->getStreet());
        self::assertEquals(25, $transit->getTo()->getBuildingNumber());
        self::assertEquals(Transit::STATUS_DRAFT, $transit->getStatus());
        self::assertNotNull($transit->getTariff());
        self::assertNotEquals(0, $transit->getTariff()->getKmRate());
        self::assertNotNull($transit->getDateTime());
        self::assertNotNull($transit->getCarType());
    }

    /**
     * @test
     */
    public function canChangeTransitDestination(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );

        //when
        $transit->changeDestinationTo(
            new Address('Polska', 'Warszawa', 'Mazowiecka', 30), Distance::ofKm(20.0));

        //then
        self::assertEquals(30, $transit->getTo()->getBuildingNumber());
        self::assertEquals('Mazowiecka', $transit->getTo()->getStreet());
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            $destination
        );
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );

        //when
        $transit->changePickupTo(
            new Address('Polska', 'Warszawa', 'Puławska', 28), Distance::ofKm(20.0), 0.2
        );

        //then
        self::assertEquals(28, $transit->getFrom()->getBuildingNumber());
        self::assertEquals('Puławska', $transit->getFrom()->getStreet());
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            $destination
        );
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );

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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );

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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            $destination
        );
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );

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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 20),
        );
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
        self::assertNotNull($transit->getAcceptedAt());
    }

    /**
     * @test
     */
    public function onlyOneDriverCanAcceptTransit(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 20),
        );
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );
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
        self::assertNotNull($transit->getStarted());
    }

    /**
     * @test
     */
    public function cannotStartNotAcceptedTransit(): void
    {
        //given
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            $destination
        );
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
        self::assertNotNull($transit->getCompleteAt());
    }

    /**
     * @test
     */
    public function cannotCompleteNotStartedTransit(): void
    {
        //given
        $destination = new Address('Polska', 'Warszawa', 'Żytnia', 25);
        //and
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            $destination
        );
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
        $transit = $this->requestTransitFromTo(
            new Address('Polska', 'Warszawa', 'Młynarska', 20),
            new Address('Polska', 'Warszawa', 'Żytnia', 25),
        );
        //and
        $driver = new Driver();
        //and
        $transit->publishAt(new \DateTimeImmutable());

        //when
        $transit->rejectBy($driver);

        //then
        self::assertEquals(Transit::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT, $transit->getStatus());
        self::assertNull($transit->getAcceptedAt());
    }

    private function requestTransitFromTo(Address $pickup, Address $destination): Transit
    {
        $transit = new Transit($pickup, $destination, new Client(), CarType::CAR_CLASS_VAN, new \DateTimeImmutable(), Distance::zero());
        PrivateProperty::setId(1, $transit);
        return $transit;
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
