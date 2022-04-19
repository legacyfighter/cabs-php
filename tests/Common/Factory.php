<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Transit;

class Factory
{
    public static function transit(int $id, ?Client $client = null): Transit
    {
        $transit = new Transit(self::client(), new \DateTimeImmutable(), Distance::zero());
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
