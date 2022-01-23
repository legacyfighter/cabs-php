<?php

namespace LegacyFighter\Cabs\Tests\Unit\Ui;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\DTO\TransitDTO;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Tests\Common\PrivateProperty;
use PHPUnit\Framework\TestCase;

class CalculateTransitDistanceTest extends TestCase
{
    /**
     * @test
     */
    public function shouldNotWorkWithInvalidUnit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->transitForDistance(2.0)->getDistance('invalid');
    }

    /**
     * @test
     */
    public function shouldRepresentAsKm(): void
    {
        self::assertEquals('10km', $this->transitForDistance(10)->getDistance('km'));
        self::assertEquals('10.123km', $this->transitForDistance(10.123)->getDistance('km'));
        self::assertEquals('10.123km', $this->transitForDistance(10.12345)->getDistance('km'));
        self::assertEquals('0km', $this->transitForDistance(0)->getDistance('km'));
    }

    /**
     * @test
     */
    public function shouldRepresentAsMeters(): void
    {
        self::assertEquals('10000m', $this->transitForDistance(10)->getDistance('m'));
        self::assertEquals('10123m', $this->transitForDistance(10.123)->getDistance('m'));
        self::assertEquals('10123m', $this->transitForDistance(10.12345)->getDistance('m'));
        self::assertEquals('0m', $this->transitForDistance(0)->getDistance('m'));
    }

    /**
     * @test
     */
    public function shouldRepresentAsMiles(): void
    {
        self::assertEquals('6.214miles', $this->transitForDistance(10)->getDistance('miles'));
        self::assertEquals('6.290miles', $this->transitForDistance(10.123)->getDistance('miles'));
        self::assertEquals('6.290miles', $this->transitForDistance(10.12345)->getDistance('miles'));
        self::assertEquals('0miles', $this->transitForDistance(0)->getDistance('miles'));
    }

    private function transitForDistance(float $km): TransitDTO
    {
        $address = new Address('country', 'city', 'street', 1);
        $address->setName('name');
        $address->setPostalCode('111');
        PrivateProperty::setId(1, $address);

        $client = new Client();
        $client->setName('Janusz');
        $client->setLastName('Kowalski');
        $client->setType(Client::TYPE_NORMAL);
        $client->setDefaultPaymentType(Client::PAYMENT_TYPE_MONTHLY_INVOICE);
        PrivateProperty::setId(1, $client);

        $t = new Transit();
        PrivateProperty::setId(1, $t);
        $t->setPrice(Money::from(10));
        $t->setDateTime(new \DateTimeImmutable());
        $t->setTo($address);
        $t->setFrom($address);
        $t->setStatus(Transit::STATUS_DRAFT);
        $t->setKm(Distance::ofKm($km));
        $t->setClient($client);
        return TransitDTO::from($t);
    }
}
