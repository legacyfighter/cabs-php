<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class DriverPosition extends BaseEntity
{
    #[ManyToOne(targetEntity: Driver::class)]
    private Driver $driver;

    #[Column(type: 'float')]
    private float $latitude;

    #[Column(type: 'float')]
    private float $longitude;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $seenAt;

    public function __construct()
    {
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function setDriver(Driver $driver): void
    {
        $this->driver = $driver;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getSeenAt(): \DateTimeImmutable
    {
        return $this->seenAt;
    }

    public function setSeenAt(\DateTimeImmutable $seenAt): void
    {
        $this->seenAt = $seenAt;
    }
}
