<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class Driver extends BaseEntity
{
    public const TYPE_CANDIDATE = 'candidate';
    public const TYPE_REGULAR = 'regular';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    #[Column]
    private string $type;

    #[Column]
    private string $status;

    #[Column]
    private string $firstName;

    #[Column]
    private string $lastName;

    #[Column(type: 'text', nullable: true)]
    private ?string $photo = null;

    #[Embedded(class: DriverLicense::class, columnPrefix: false)]
    private DriverLicense $driverLicense;

    #[OneToOne(targetEntity: DriverFee::class)]
    private DriverFee $fee;

    #[Column(type: 'boolean')]
    private bool $isOccupied = false;

    /**
     * @var Collection<DriverAttribute>
     */
    #[OneToMany(mappedBy: 'driver', targetEntity: DriverAttribute::class)]
    private Collection $attributes;

    /**
     * @var Collection<Transit>
     */
    #[OneToMany(mappedBy: 'driver', targetEntity: Transit::class)]
    private Collection $transits;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->transits = new ArrayCollection();
    }

    public function calculateEarningsForTransit(Transit $transit): void
    {
        // zdublować kod wyliczenia kosztu przejazdu
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if(!in_array($type, [self::TYPE_CANDIDATE, self::TYPE_REGULAR], true)) {
            throw new \InvalidArgumentException('Invalid driver type');
        }
        $this->type = $type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        if(!in_array($status, [self::STATUS_ACTIVE, self::STATUS_INACTIVE], true)) {
            throw new \InvalidArgumentException('Invalid driver status');
        }
        $this->status = $status;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): void
    {
        $this->photo = $photo;
    }

    public function getDriverLicense(): DriverLicense
    {
        return $this->driverLicense;
    }

    public function setDriverLicense(DriverLicense $driverLicense): void
    {
        $this->driverLicense = $driverLicense;
    }

    public function getFee(): DriverFee
    {
        return $this->fee;
    }

    public function setFee(DriverFee $fee): void
    {
        $this->fee = $fee;
    }

    public function getOccupied(): bool
    {
        return $this->isOccupied;
    }

    public function setOccupied(bool $isOccupied): void
    {
        $this->isOccupied = $isOccupied;
    }

    public function getAttributes(): array
    {
        return $this->attributes->toArray();
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = new ArrayCollection($attributes);
    }

    public function getTransits(): array
    {
        return $this->transits->toArray();
    }

    public function setTransits(array $transits): void
    {
        $this->transits = new ArrayCollection($transits);
    }
}
