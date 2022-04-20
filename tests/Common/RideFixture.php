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
use LegacyFighter\Cabs\Ride\RideService;

class RideFixture
{
    public function __construct(
        private RideService          $rideService,
        private AddressRepository    $addressRepository,
        private CarTypeFixture       $carTypeFixture,
        private StubbedTransitPrice  $stubbedPrice,
        private TransitDetailsFacade $transitDetailsFacade
    )
    {
    }

    public function aRide(int $price, Client $client, Driver $driver, Address $from, Address $destination): TransitDTO
    {
        $from = $this->addressRepository->save($from);
        $destination = $this->addressRepository->save($destination);
        $this->carTypeFixture->anActiveCarCategory(CarType::CAR_CLASS_VAN);
        $transit = $this->rideService->createTransitFrom($client->getId(), AddressDTO::from($from), AddressDTO::from($destination), CarType::CAR_CLASS_VAN);
        $this->rideService->publishTransit($transit->getRequestUuid());
        $this->rideService->findDriversForTransit($transit->getRequestUuid());
        $this->rideService->acceptTransit($driver->getId(), $transit->getRequestUuid());
        $this->rideService->startTransit($driver->getId(), $transit->getRequestUuid());
        $this->rideService->completeTransit($driver->getId(), $transit->getRequestUuid(), AddressDTO::from($destination));
        $this->transitDetailsFacade->transitCompleted($transit->getRequestUuid(), new \DateTimeImmutable(), Money::from($price), Money::from($price));
        $this->stubbedPrice->stub(Money::from($price));
        return $this->rideService->loadTransit($transit->getRequestUuid());
    }

    public function aRideWithFixedClock(int $price, \DateTimeImmutable $publishedAt, \DateTimeImmutable $completedAt, Client $client, Driver $driver, Address $from, Address $destination, FixedClock $clock): void
    {
        $from = $this->addressRepository->save($from);
        $destination = $this->addressRepository->save($destination);
        $clock->setDateTime($publishedAt);
        $this->carTypeFixture->anActiveCarCategory(CarType::CAR_CLASS_VAN);
        $transit = $this->rideService->createTransitFrom($client->getId(), AddressDTO::from($from), AddressDTO::from($destination), CarType::CAR_CLASS_VAN);
        $this->rideService->publishTransit($transit->getRequestUuid());
        $this->rideService->findDriversForTransit($transit->getRequestUuid());
        $this->rideService->acceptTransit($driver->getId(), $transit->getRequestUuid());
        $this->rideService->startTransit($driver->getId(), $transit->getRequestUuid());
        $clock->setDateTime($completedAt);
        $this->rideService->completeTransit($driver->getId(), $transit->getRequestUuid(), AddressDTO::from($destination));
        $this->transitDetailsFacade->transitCompleted($transit->getRequestUuid(), new \DateTimeImmutable(), Money::from($price), Money::from($price));
        $this->stubbedPrice->stub(Money::from($price));
    }
}
