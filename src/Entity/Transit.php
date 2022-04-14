<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\Money\Money;

#[Entity]
class Transit extends BaseEntity
{
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

    #[Column(type: 'integer')]
    private int $pickupAddressChangeCounter = 0;

    #[ManyToOne(targetEntity: Driver::class)]
    private ?Driver $driver = null;

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

    #[Embedded(class: Tariff::class)]
    private Tariff $tariff;

    #[Column(type: 'float', nullable: true)]
    private ?float $km = null;

    // https://stackoverflow.com/questions/37107123/sould-i-store-price-as-decimal-or-integer-in-mysql
    #[Column(type: 'money', nullable: true)]
    private ?Money $price = null;

    #[Column(type: 'money', nullable: true)]
    private ?Money $estimatedPrice = null;

    #[Column(type: 'money', nullable: true)]
    private ?Money $driversFee = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $published = null;

    #[ManyToOne(targetEntity: Client::class)]
    private Client $client;

    public function __construct(Client $client, \DateTimeImmutable $when, Distance $distance)
    {
        $this->client = $client;
        $this->setDateTime($when);
        $this->km = $distance->toKmInFloat();
        $this->setStatus(self::STATUS_DRAFT);
        $this->proposedDrivers = new ArrayCollection();
        $this->driversRejections = new ArrayCollection();
    }

    public static function withStatus(string $status, Client $client, \DateTimeImmutable $when, Distance $distance): self
    {
        $transit = new self($client, $when, $distance);
        $transit->setStatus($status);

        return $transit;
    }

    public function changePickupTo(Address $newAddress, Distance $newDistance, float $distanceFromPreviousPickup): void
    {
        if($distanceFromPreviousPickup > 0.25) {
            throw new \InvalidArgumentException('Address \'from\' cannot be changed, id ='.$this->getId());
        }

        if(!in_array($this->status, [self::STATUS_DRAFT, self::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT], true)) {
            throw new \InvalidArgumentException('Address \'from\' cannot be changed, id ='.$this->getId());
        }

        if($this->pickupAddressChangeCounter > 2) {
            throw new \InvalidArgumentException('Address \'from\' cannot be changed, id ='.$this->getId());
        }

        $this->pickupAddressChangeCounter++;
        $this->km = $newDistance->toKmInFloat();
        $this->estimateCost();
    }

    public function changeDestinationTo(Address $newAddress, Distance $newDistance): void
    {
        if($this->status === self::STATUS_COMPLETED) {
            throw new \InvalidArgumentException('Address \'to\' cannot be changed, id ='.$this->getId());
        }

        $this->km = $newDistance->toKmInFloat();
        $this->estimateCost();
    }

    public function cancel(): void
    {
        if(!in_array($this->status, [self::STATUS_DRAFT, self::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT, self::STATUS_TRANSIT_TO_PASSENGER], true)) {
            throw new \InvalidArgumentException('Transit cannot be cancelled, id = '.$this->getId());
        }

        $this->status = self::STATUS_CANCELLED;
        $this->driver = null;
        $this->km = Distance::zero()->toKmInFloat();
        $this->awaitingDriversResponses = 0;
    }

    public function canProposeTo(Driver $driver): bool
    {
        return !$this->driversRejections->contains($driver);
    }

    public function proposeTo(Driver $driver): void
    {
        if($this->canProposeTo($driver)) {
            $this->proposedDrivers->add($driver);
            $this->awaitingDriversResponses++;
        }
    }

    public function failDriverAssignment(): void
    {
        $this->status = self::STATUS_DRIVER_ASSIGNMENT_FAILED;
        $this->driver = null;
        $this->km = Distance::zero()->toKmInFloat();
        $this->awaitingDriversResponses = 0;
    }

    public function shouldNotWaitForDriverAnyMore(\DateTimeImmutable $date): bool
    {
        return $this->status === self::STATUS_CANCELLED || $this->getPublished()->modify('+300 seconds') < $date;
    }

    public function acceptBy(Driver $driver, \DateTimeImmutable $when): void
    {
        if($this->driver !== null) {
            throw new \RuntimeException('Transit already accepted, id = '.$this->getId());
        }
        if(!$this->proposedDrivers->contains($driver)) {
            throw new \RuntimeException('Driver out of possible drivers, id = '.$this->getId());
        }
        if($this->driversRejections->contains($driver)) {
            throw new \RuntimeException('Driver out of possible drivers, id = '.$this->getId());
        }

        $this->driver = $driver;
        $this->driver->setOccupied(true);
        $this->awaitingDriversResponses = 0;
        $this->status = self::STATUS_TRANSIT_TO_PASSENGER;
    }

    public function start(\DateTimeImmutable $when): void
    {
        if($this->status !== self::STATUS_TRANSIT_TO_PASSENGER) {
            throw new \InvalidArgumentException('Transit cannot be started, id = '.$this->getId());
        }
        $this->status = self::STATUS_IN_TRANSIT;
    }

    public function rejectBy(Driver $driver): void
    {
        $this->driversRejections->add($driver);
        $this->awaitingDriversResponses--;
    }

    public function publishAt(\DateTimeImmutable $when): void
    {
        $this->status = self::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT;
        $this->published = $when;
    }

    public function completeAt(\DateTimeImmutable $when, Address $destinationAddress, Distance $distance): void
    {
        if($this->status !== self::STATUS_IN_TRANSIT) {
            throw new \RuntimeException('Cannot complete Transit, id = '.$this->getId());
        }

        $this->km = $distance->toKmInFloat();
        $this->estimateCost();
        $this->status = self::STATUS_COMPLETED;
        $this->calculateFinalCosts();
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

    private function setStatus(string $status): void
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

    public function getProposedDrivers(): array
    {
        return $this->proposedDrivers->toArray();
    }

    public function getAwaitingDriversResponses(): int
    {
        return $this->awaitingDriversResponses;
    }

    public function getKm(): ?Distance
    {
        return $this->km === null ? null : Distance::ofKm($this->km);
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    //just for testing
    public function setPrice(?Money $price): void
    {
        $this->price = $price;
    }

    public function getEstimatedPrice(): ?Money
    {
        return $this->estimatedPrice;
    }

    public function getDriversFee(): ?Money
    {
        return $this->driversFee;
    }

    public function setDriversFee(?Money $driversFee): void
    {
        $this->driversFee = $driversFee;
    }

    public function setDateTime(?\DateTimeImmutable $dateTime): void
    {
        $this->tariff = Tariff::ofTime($dateTime);
    }

    public function getPublished(): ?\DateTimeImmutable
    {
        return $this->published;
    }

    public function estimateCost(): Money
    {
        if($this->status === self::STATUS_COMPLETED) {
            throw new \RuntimeException('Estimating cost for completed transit is forbidden, id = ', $this->id);
        }

        $estimated = $this->calculateCost();
        $this->estimatedPrice = $estimated;
        $this->price = null;

        return $this->estimatedPrice;
    }

    public function calculateFinalCosts(): Money
    {
        if($this->status === self::STATUS_COMPLETED) {
            return $this->calculateCost();
        } else {
            throw new \RuntimeException('Cannot calculate final cost if the transit is not completed');
        }
    }

    private function calculateCost(): Money
    {
        $money = $this->tariff->calculateCost(Distance::ofKm($this->km));
        $this->price = $money;
        return $money;
    }

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function getTariff(): Tariff
    {
        return $this->tariff;
    }
}
