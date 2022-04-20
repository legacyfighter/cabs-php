<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\CarFleet\CarType;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Ride\Details\TransitDetails;
use LegacyFighter\Cabs\Ride\Details\TransitDetailsDTO;
use LegacyFighter\Cabs\Ride\Details\TransitDetailsFacade;
use LegacyFighter\Cabs\Ride\Transit;
use LegacyFighter\Cabs\Ride\TransitDTO;
use LegacyFighter\Cabs\Ride\TransitRepository;
use Symfony\Component\Uid\Uuid;

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
        $transit = $this->transitRepository->save(new Transit(Tariff::of(0, 'fake', Money::from($price)), Uuid::v4()));
        $this->stubbedTransitPrice->stub(Money::from($price));
        $this->transitDetailsFacade->transitRequested($when, $transit->getRequestUuid(), $from, $to, Distance::zero(), $client, CarType::CAR_CLASS_VAN, Money::from($price), Tariff::ofTime($when));
        $this->transitDetailsFacade->transitAccepted($transit->getRequestUuid(), $when, $driver->getId());
        $this->transitDetailsFacade->transitStarted($transit->getRequestUuid(), $transit->getId(), $when);
        $this->transitDetailsFacade->transitCompleted($transit->getRequestUuid(), $when, Money::from($price), Money::zero());
        return $transit;
    }
}
