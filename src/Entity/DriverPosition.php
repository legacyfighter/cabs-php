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

    public function __construct(Driver $driver, float $latitude, float $longitude, \DateTimeImmutable $seenAt)
    {
        $this->driver = $driver;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->seenAt = $seenAt;
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getSeenAt(): \DateTimeImmutable
    {
        return $this->seenAt;
    }
}
