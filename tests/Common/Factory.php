<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Ride\Transit;
use Symfony\Component\Uid\Uuid;

class Factory
{
    public static function transit(int $id): Transit
    {
        $transit = new Transit(Tariff::ofTime(new \DateTimeImmutable()), Uuid::v4());
        PrivateProperty::setId($id, $transit);
        return $transit;
    }

    public static function address(): Address
    {
        return new Address('country', 'city', 'street', 1);
    }

    public static function client(): Client
    {
        return new Client();
    }
}
