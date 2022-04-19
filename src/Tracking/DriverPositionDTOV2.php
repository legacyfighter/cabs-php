<?php

namespace LegacyFighter\Cabs\Tracking;

class DriverPositionDTOV2
{
    private int $driverId;
    private float $latitude;
    private float $longitude;
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
