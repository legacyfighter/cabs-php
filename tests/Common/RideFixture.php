<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\DTO\AddressDTO;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\CarType;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repository\AddressRepository;
use LegacyFighter\Cabs\Service\TransitService;

class RideFixture
{
    public function __construct(
        private TransitService $transitService,
        private AddressRepository $addressRepository,
        private CarTypeFixture $carTypeFixture,
        private StubbedTransitPrice $stubbedPrice
    )
    {
    }

    public function aRide(int $price, Client $client, Driver $driver, Address $from, Address $destination): Transit
    {
        $from = $this->addressRepository->save($from);
        $destination = $this->addressRepository->save($destination);
        $this->carTypeFixture->anActiveCarCategory(CarType::CAR_CLASS_VAN);
        $transit = $this->transitService->createTransitFrom($client->getId(), $from, $destination, CarType::CAR_CLASS_VAN);
        $this->transitService->publishTransit($transit->getId());
        $this->transitService->findDriversForTransit($transit->getId());
        $this->transitService->acceptTransit($driver->getId(), $transit->getId());
        $this->transitService->startTransit($driver->getId(), $transit->getId());
        $this->transitService->completeTransit($driver->getId(), $transit->getId(), AddressDTO::from($destination));
        return $this->stubbedPrice->stub($transit->getId(), Money::from($price));
    }

    public function aRideWithFixedClock(int $price, \DateTimeImmutable $publishedAt, \DateTimeImmutable $completedAt, Client $client, Driver $driver, Address $from, Address $destination, FixedClock $clock): Transit
    {
        $from = $this->addressRepository->save($from);
        $destination = $this->addressRepository->save($destination);
        $clock->setDateTime($publishedAt);
        $this->carTypeFixture->anActiveCarCategory(CarType::CAR_CLASS_VAN);
        $transit = $this->transitService->createTransitFrom($client->getId(), $from, $destination, CarType::CAR_CLASS_VAN);
        $this->transitService->publishTransit($transit->getId());
        $this->transitService->findDriversForTransit($transit->getId());
        $this->transitService->acceptTransit($driver->getId(), $transit->getId());
        $this->transitService->startTransit($driver->getId(), $transit->getId());
        $clock->setDateTime($completedAt);
        $this->transitService->completeTransit($driver->getId(), $transit->getId(), AddressDTO::from($destination));
        return $this->stubbedPrice->stub($transit->getId(), Money::from($price));
    }
}
