<?php

namespace LegacyFighter\Cabs\Tracking;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class DriverPosition extends BaseEntity
{
    #[Column(type: 'integer')]
    private int $driverId;

    #[Column(type: 'float')]
    private float $latitude;

    #[Column(type: 'float')]
    private float $longitude;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $seenAt;

    public function __construct(int $driverId, float $latitude, float $longitude, \DateTimeImmutable $seenAt)
    {
        $this->driverId = $driverId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->seenAt = $seenAt;
    }

    public function getDriverId(): int
    {
        return $this->driverId;
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
