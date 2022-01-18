<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\Driver;

class DriverPositionDTOV2
{
    private Driver $driver;
    private float $latitude;
    private float $longitude;
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
