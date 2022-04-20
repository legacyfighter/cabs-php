<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\CarFleet\CarType;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Address\AddressRepository;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Ride\Details\TransitDetailsFacade;
use LegacyFighter\Cabs\Ride\Transit;
use LegacyFighter\Cabs\Ride\TransitDTO;
use LegacyFighter\Cabs\Ride\TransitRepository;
use LegacyFighter\Cabs\Ride\TransitService;

class RideFixture
{
    public function __construct(
        private TransitService $transitService,
        private AddressRepository $addressRepository,
        private CarTypeFixture $carTypeFixture,
        private StubbedTransitPrice $stubbedPrice,
        private TransitDetailsFacade $transitDetailsFacade
    )
    {
    }

    public function aRide(int $price, Client $client, Driver $driver, Address $from, Address $destination): TransitDTO
    {
        $from = $this->addressRepository->save($from);
        $destination = $this->addressRepository->save($destination);
        $this->carTypeFixture->anActiveCarCategory(CarType::CAR_CLASS_VAN);
        $transit = $this->transitService->createTransitFrom($client->getId(), $from, $destination, CarType::CAR_CLASS_VAN);
        $this->transitService->publishTransit($transit->getRequestUuid());
        $this->transitService->findDriversForTransit($transit->getRequestUuid());
        $this->transitService->acceptTransit($driver->getId(), $transit->getRequestUuid());
        $this->transitService->startTransit($driver->getId(), $transit->getRequestUuid());
        $this->transitService->completeTransit($driver->getId(), $transit->getRequestUuid(), AddressDTO::from($destination));
        $this->transitDetailsFacade->transitCompleted($transit->getRequestUuid(), new \DateTimeImmutable(), Money::from($price), Money::from($price));
        $this->stubbedPrice->stub(Money::from($price));
        return $this->transitService->loadTransit($transit->getRequestUuid());
    }

    public function aRideWithFixedClock(int $price, \DateTimeImmutable $publishedAt, \DateTimeImmutable $completedAt, Client $client, Driver $driver, Address $from, Address $destination, FixedClock $clock): void
    {
        $from = $this->addressRepository->save($from);
        $destination = $this->addressRepository->save($destination);
        $clock->setDateTime($publishedAt);
        $this->carTypeFixture->anActiveCarCategory(CarType::CAR_CLASS_VAN);
        $transit = $this->transitService->createTransitFrom($client->getId(), $from, $destination, CarType::CAR_CLASS_VAN);
        $this->transitService->publishTransit($transit->getRequestUuid());
        $this->transitService->findDriversForTransit($transit->getRequestUuid());
        $this->transitService->acceptTransit($driver->getId(), $transit->getRequestUuid());
        $this->transitService->startTransit($driver->getId(), $transit->getRequestUuid());
        $clock->setDateTime($completedAt);
        $this->transitService->completeTransit($driver->getId(), $transit->getRequestUuid(), AddressDTO::from($destination));
        $this->transitDetailsFacade->transitCompleted($transit->getRequestUuid(), new \DateTimeImmutable(), Money::from($price), Money::from($price));
        $this->stubbedPrice->stub(Money::from($price));
    }
}
