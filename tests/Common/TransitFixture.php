<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\DTO\AddressDTO;
use LegacyFighter\Cabs\DTO\TransitDTO;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\CarType;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\Tariff;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repository\TransitRepository;
use LegacyFighter\Cabs\TransitDetails\TransitDetails;
use LegacyFighter\Cabs\TransitDetails\TransitDetailsDTO;
use LegacyFighter\Cabs\TransitDetails\TransitDetailsFacade;

class TransitFixture
{
    public function __construct(
        private TransitRepository $transitRepository,
        private TransitDetailsFacade $transitDetailsFacade,
        private StubbedTransitPrice $stubbedTransitPrice
    )
    {
    }

    public function transitDetails(Driver $driver, int $price, \DateTimeImmutable $when, Client $client, Address $from, Address $to): Transit
    {
        $transit = $this->transitRepository->save(new Transit($client, $when, Distance::zero()));
        $this->stubbedTransitPrice->stub($transit->getId(), Money::from($price));
        $this->transitDetailsFacade->transitRequested($when, $transit->getId(), $from, $to, Distance::zero(), $client, CarType::CAR_CLASS_VAN, Money::from($price), Tariff::ofTime($when));
        $this->transitDetailsFacade->transitAccepted($transit->getId(), $when, $driver->getId());
        $this->transitDetailsFacade->transitStarted($transit->getId(), $when);
        $this->transitDetailsFacade->transitCompleted($transit->getId(), $when, Money::from($price), Money::zero());
        return $transit;
    }

    public function aTransitDTO(Client $client, AddressDTO $from, AddressDTO $to): TransitDTO
    {
        $transit = new Transit($client, new \DateTimeImmutable(), Distance::zero());
        PrivateProperty::setId(1, $transit);
        $transitDetails = new TransitDetails(new \DateTimeImmutable(), 1, $from->toAddressEntity(), $to->toAddressEntity(), Distance::zero(), $client, CarType::CAR_CLASS_VAN, Money::zero(), $transit->getTariff());
        PrivateProperty::setId(1, $transitDetails);

        return TransitDTO::from($transit, TransitDetailsDTO::from($transitDetails));
    }
}
