<?php

namespace LegacyFighter\Cabs\Ride\Details;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use LegacyFighter\Cabs\Assignment\AssignmentStatus;
use LegacyFighter\Cabs\Assignment\InvolvedDriversSummary;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Ride\Transit;
use Symfony\Component\Uid\Uuid;

#[Entity]
class TransitDetails extends BaseEntity
{
    #[Column(type: 'integer', nullable: true)]
    private ?int $transitId = null;

    #[Column(type: 'uuid')]
    private Uuid $requestUuid;

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
        Uuid $requestUuid,
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
        $this->requestUuid = $requestUuid;
        $this->from = $from;
        $this->to = $to;
        $this->distance = $distance;
        $this->client = $client;
        $this->carType = $carClass;
        $this->estimatedPrice = $estimatedPrice;
        $this->tariff = $tariff;
        $this->status = Status::DRAFT;
    }

    public function startedAt(\DateTimeImmutable $when, int $transitId): void
    {
        $this->started = $when;
        $this->status = Status::IN_TRANSIT;
        $this->transitId = $transitId;
    }

    public function acceptedAt(\DateTimeImmutable $when, int $driverId): void
    {
        $this->acceptedAt = $when;
        $this->driverId = $driverId;
        $this->status = Status::TRANSIT_TO_PASSENGER;
    }

    public function publishedAt(\DateTimeImmutable $when): void
    {
        $this->publishedAt = $when;
        $this->status = Status::WAITING_FOR_DRIVER_ASSIGNMENT;
    }

    public function completedAt(\DateTimeImmutable $when, Money $price, Money $driverFee): void
    {
        $this->completedAt = $when;
        $this->price = $price;
        $this->driversFee = $driverFee;
        $this->status = Status::COMPLETED;
    }

    public function cancelled(): void
    {
        $this->status = Status::CANCELLED;
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

    public function involvedDriversAre(InvolvedDriversSummary $involvedDriversSummary): void
    {
        if($involvedDriversSummary->status === AssignmentStatus::DRIVER_ASSIGNMENT_FAILED) {
            $this->status = Status::DRIVER_ASSIGNMENT_FAILED;
        } else {
            $this->status = Status::TRANSIT_TO_PASSENGER;
        }
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getEstimatedPrice(): ?Money
    {
        return $this->estimatedPrice;
    }

    public function getRequestUuid(): Uuid
    {
        return $this->requestUuid;
    }

    public function getTransitId(): ?int
    {
        return $this->transitId;
    }
}
