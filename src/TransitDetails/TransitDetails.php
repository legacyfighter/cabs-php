<?php

namespace LegacyFighter\Cabs\TransitDetails;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\Entity\Tariff;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Money\Money;

#[Entity]
class TransitDetails extends BaseEntity
{
    #[Column(type: 'integer')]
    private int $transitId;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateTime;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ManyToOne(targetEntity: Client::class)]
    private Client $client;

    #[Column(nullable: true)]
    private ?string $carType = null;

    #[ManyToOne(targetEntity: Address::class)]
    private Address $from;

    #[ManyToOne(targetEntity: Address::class)]
    private Address $to;

    #[Column(type: 'distance')]
    private Distance $distance;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $started = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $acceptedAt = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[Column(type: 'money', nullable: true)]
    private ?Money $price = null;

    #[Column(type: 'money', nullable: true)]
    private ?Money $estimatedPrice = null;

    #[Column(type: 'money', nullable: true)]
    private ?Money $driversFee = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $driverId = null;

    #[Column]
    private string $status;

    #[Embedded(class: Tariff::class)]
    private Tariff $tariff;

    public function __construct(
        \DateTimeImmutable $dateTime,
        int $transitId,
        Address $from,
        Address $to,
        Distance $distance,
        Client $client,
        string $carClass,
        Money $estimatedPrice,
        Tariff $tariff
    )
    {
        $this->dateTime = $dateTime;
        $this->transitId = $transitId;
        $this->from = $from;
        $this->to = $to;
        $this->distance = $distance;
        $this->client = $client;
        $this->carType = $carClass;
        $this->estimatedPrice = $estimatedPrice;
        $this->tariff = $tariff;
        $this->status = Transit::STATUS_DRAFT;
    }

    public function startedAt(\DateTimeImmutable $when): void
    {
        $this->started = $when;
        $this->status = Transit::STATUS_IN_TRANSIT;
    }

    public function acceptedAt(\DateTimeImmutable $when, int $driverId): void
    {
        $this->acceptedAt = $when;
        $this->driverId = $driverId;
        $this->status = Transit::STATUS_TRANSIT_TO_PASSENGER;
    }

    public function publishedAt(\DateTimeImmutable $when): void
    {
        $this->publishedAt = $when;
        $this->status = Transit::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT;
    }

    public function completedAt(\DateTimeImmutable $when, Money $price, Money $driverFee): void
    {
        $this->completedAt = $when;
        $this->price = $price;
        $this->driversFee = $driverFee;
        $this->status = Transit::STATUS_COMPLETED;
    }

    public function cancelled(): void
    {
        $this->status = Transit::STATUS_CANCELLED;
    }

    public function pickupChangedTo(Address $newAddress, Distance $newDistance): void
    {
        $this->from = $newAddress;
        $this->distance = $newDistance;
    }

    public function destinationChangedTo(Address $newAddress, Distance $distance): void
    {
        $this->to = $newAddress;
        $this->distance = $distance;
    }

    public function getKmRate(): float
    {
        return $this->tariff->getKmRate();
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getCarType(): ?string
    {
        return $this->carType;
    }

    public function getFrom(): Address
    {
        return $this->from;
    }

    public function getTo(): Address
    {
        return $this->to;
    }

    public function getDistance(): Distance
    {
        return $this->distance;
    }

    public function getStarted(): ?\DateTimeImmutable
    {
        return $this->started;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getDriverId(): ?int
    {
        return $this->driverId;
    }

    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    public function getDriversFee(): ?Money
    {
        return $this->driversFee;
    }
}
