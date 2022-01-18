<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class Transit extends BaseEntity
{
    public const BASE_FEE = 8;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_WAITING_FOR_DRIVER_ASSIGNMENT = 'waiting-for-driver-assigment';
    public const STATUS_DRIVER_ASSIGNMENT_FAILED = 'driver-assigment-failed';
    public const STATUS_TRANSIT_TO_PASSENGER = 'transit-to-passenger';
    public const STATUS_IN_TRANSIT = 'in-transit';
    public const STATUS_COMPLETED = 'completed';

    public const DRIVER_PAYMENT_STATUS_NOT_PAID = 'not-paid';
    public const DRIVER_PAYMENT_STATUS_PAID = 'paid';
    public const DRIVER_PAYMENT_STATUS_CLAIMED = 'claimed';
    public const DRIVER_PAYMENT_STATUS_RETURNED = 'returned';

    public const CLIENT_PAYMENT_STATUS_NOT_PAID = 'not-paid';
    public const CLIENT_PAYMENT_STATUS_PAID = 'paid';
    public const CLIENT_PAYMENT_STATUS_RETURNED = 'returned';

    #[Column(nullable: true)]
    private ?string $driverPaymentStatus = null;

    #[Column(nullable: true)]
    private ?string $clientPaymentStatus = null;

    #[Column(nullable: true)]
    private ?string $paymentType = null;

    #[Column]
    private string $status;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $date = null;

    #[ManyToOne(targetEntity: Address::class)]
    private Address $from;

    #[ManyToOne(targetEntity: Address::class)]
    private Address $to;

    #[Column(type: 'integer')]
    private int $pickupAddressChangeCounter = 0;

    #[ManyToOne(targetEntity: Driver::class)]
    private ?Driver $driver = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $acceptedAt = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $started = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completeAt = null;

    /**
     * @var Collection<Driver>
     */
    #[ManyToMany(targetEntity: Driver::class)]
    #[JoinTable(name: 'transit_driver_rejected')]
    private Collection $driversRejections;

    /**
     * @var Collection<Driver>
     */
    #[ManyToMany(targetEntity: Driver::class)]
    #[JoinTable(name: 'transit_driver_proposed')]
    private Collection $proposedDrivers;

    #[Column(type: 'integer')]
    private int $awaitingDriversResponses = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $factor = null;

    #[Column(type: 'float', nullable: true)]
    private ?float $km = null;

    // https://stackoverflow.com/questions/37107123/sould-i-store-price-as-decimal-or-integer-in-mysql
    #[Column(type: 'integer', nullable: true)]
    private ?int $price = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $estimatedPrice = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $driversFee = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateTime = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $published = null;

    #[ManyToOne(targetEntity: Client::class)]
    private Client $client;

    #[Column(nullable: true)]
    private ?string $carType = null;

    public function __construct()
    {
        $this->proposedDrivers = new ArrayCollection();
        $this->driversRejections = new ArrayCollection();
    }

    public function getDriverPaymentStatus(): ?string
    {
        return $this->driverPaymentStatus;
    }

    public function setDriverPaymentStatus(string $driverPaymentStatus): void
    {
        $this->driverPaymentStatus = $driverPaymentStatus;
    }

    public function getClientPaymentStatus(): ?string
    {
        return $this->clientPaymentStatus;
    }

    public function setClientPaymentStatus(string $clientPaymentStatus): void
    {
        $this->clientPaymentStatus = $clientPaymentStatus;
    }

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(string $paymentType): void
    {
        $this->paymentType = $paymentType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        if(!in_array($status, [
            self::STATUS_IN_TRANSIT,
            self::STATUS_TRANSIT_TO_PASSENGER,
            self::STATUS_DRIVER_ASSIGNMENT_FAILED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED,
            self::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT,
            self::STATUS_DRAFT
        ], true)) {
            throw new \InvalidArgumentException('Invalid driver status value');
        }
        $this->status = $status;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getFrom(): Address
    {
        return $this->from;
    }

    public function setFrom(Address $from): void
    {
        $this->from = $from;
    }

    public function getTo(): Address
    {
        return $this->to;
    }

    public function setTo(Address $to): void
    {
        $this->to = $to;
    }

    public function getPickupAddressChangeCounter(): int
    {
        return $this->pickupAddressChangeCounter;
    }

    public function setPickupAddressChangeCounter(int $pickupAddressChangeCounter): void
    {
        $this->pickupAddressChangeCounter = $pickupAddressChangeCounter;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(?\DateTimeImmutable $acceptedAt): void
    {
        $this->acceptedAt = $acceptedAt;
    }

    public function getStarted(): ?\DateTimeImmutable
    {
        return $this->started;
    }

    public function setStarted(?\DateTimeImmutable $started): void
    {
        $this->started = $started;
    }

    public function getDriversRejections(): array
    {
        return $this->driversRejections->toArray();
    }

    public function setDriversRejections(array $driversRejections): void
    {
        $this->driversRejections = new ArrayCollection($driversRejections);
    }

    public function getProposedDrivers(): array
    {
        return $this->proposedDrivers->toArray();
    }

    public function setProposedDrivers(array $proposedDrivers): void
    {
        $this->proposedDrivers = new ArrayCollection($proposedDrivers);
    }

    public function getAwaitingDriversResponses(): int
    {
        return $this->awaitingDriversResponses;
    }

    public function setAwaitingDriversResponses(int $awaitingDriversResponses): void
    {
        $this->awaitingDriversResponses = $awaitingDriversResponses;
    }

    public function getFactor(): ?int
    {
        return $this->factor;
    }

    public function setFactor(int $factor): void
    {
        $this->factor = $factor;
    }

    public function getKm(): ?float
    {
        return $this->km;
    }

    public function setKm(float $km): void
    {
        $this->km = $km;
        $this->estimateCost();
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    //just for testing
    public function setPrice(?int $price): void
    {
        $this->price = $price;
    }

    public function getEstimatedPrice(): ?int
    {
        return $this->estimatedPrice;
    }

    public function setEstimatedPrice(?int $estimatedPrice): void
    {
        $this->estimatedPrice = $estimatedPrice;
    }

    public function getDriversFee(): ?int
    {
        return $this->driversFee;
    }

    public function setDriversFee(?int $driversFee): void
    {
        $this->driversFee = $driversFee;
    }

    public function getDateTime(): ?\DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function setDateTime(?\DateTimeImmutable $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function getPublished(): ?\DateTimeImmutable
    {
        return $this->published;
    }

    public function setPublished(?\DateTimeImmutable $published): void
    {
        $this->published = $published;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function estimateCost(): int
    {
        if($this->status === self::STATUS_COMPLETED) {
            throw new \RuntimeException('Estimating cost for completed transit is forbidden, id = ', $this->id);
        }

        $estimated = $this->calculateCost();
        $this->estimatedPrice = $estimated;
        $this->price = null;

        return $this->estimatedPrice;
    }

    public function calculateFinalCosts(): int
    {
        if($this->status === self::STATUS_COMPLETED) {
            return $this->calculateCost();
        } else {
            throw new \RuntimeException('Cannot calculate final cost if the transit is not completed');
        }
    }

    private function calculateCost(): int
    {
        $baseFee = self::BASE_FEE;
        $factorToCalculate = $this->factor;
        if($factorToCalculate == null) {
            $factorToCalculate = 1;
        }
        $day = $this->dateTime;
        // wprowadzenie nowych cennikow od 1.01.2019
        if((int) $day->format('Y') <= 2018) {
            $kmRate = 1.0;
            $baseFee++;
        } else {
            if(($day->format('n') === '12' && $day->format('j') === '31') ||
                ($day->format('n') === '1' && $day->format('j') === '1' && (int) $day->format('G') <= 6)
            ) {
                $kmRate = 3.50;
                $baseFee += 3;
            } else {
                // piątek i sobota po 17 do 6 następnego dnia
                if(($day->format('l') === 'Friday' && (int) $day->format('G') >= 17) ||
                    ($day->format('l') === 'Saturday' && (int) $day->format('G') <= 6) ||
                    ($day->format('l') === 'Saturday' && (int) $day->format('G') >= 17) ||
                    ($day->format('l') === 'Sunday' && (int) $day->format('G') <= 6)
                ) {
                    $kmRate = 2.50;
                    $baseFee += 2;
                } else {
                    // pozostałe godziny weekendu
                    if(($day->format('l') === 'Saturday' && (int) $day->format('G') > 6 && (int) $day->format('G') < 17) ||
                        ($day->format('l') === 'Sunday' && (int) $day->format('G') > 6)
                    ) {
                        $kmRate = 1.5;
                    } else {
                        // tydzień roboczy
                        $kmRate = 1.0;
                        $baseFee++;
                    }
                }
            }
        }

        $finalPrice = (int) ceil(($this->km * $kmRate * $factorToCalculate + $baseFee) * 100);
        $this->price = $finalPrice;
        return $this->price;
    }

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): void
    {
        $this->driver = $driver;
    }

    public function getCompleteAt(): ?\DateTimeImmutable
    {
        return $this->completeAt;
    }

    public function setCompleteAt(?\DateTimeImmutable $completeAt): void
    {
        $this->completeAt = $completeAt;
    }

    public function getCarType(): ?string
    {
        return $this->carType;
    }

    public function setCarType(?string $carType): void
    {
        $this->carType = $carType;
    }
}
