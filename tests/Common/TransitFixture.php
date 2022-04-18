<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\DTO\AddressDTO;
use LegacyFighter\Cabs\DTO\TransitDTO;
use LegacyFighter\Cabs\Entity\CarType;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Driver;
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
        private AddressFixture $addressFixture
    )
    {
    }

    public function aTransit(Driver $driver, int $price, \DateTimeImmutable $when = null, Client $client = null): Transit
    {
        $transit = new Transit($client, $when, Distance::zero());
        $transit->setPrice(Money::from($price));
        $transit->proposeTo($driver);
        $transit->acceptBy($driver, new \DateTimeImmutable());
        $transit = $this->transitRepository->save($transit);
        $this->transitDetailsFacade->transitRequested($when, $transit->getId(), $this->addressFixture->anAddress('Polska', 'Warszawa', 'Zytnia', 20), $this->addressFixture->anAddress('Polska', 'Warszawa', 'Młynarska', 20), Distance::zero(), $client, CarType::CAR_CLASS_VAN, $transit->getPrice(), $transit->getTariff());
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
