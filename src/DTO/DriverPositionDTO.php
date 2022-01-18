<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\Driver;

class DriverPositionDTO implements \JsonSerializable
{
    private int $driverId;
    private float $latitude;
    private float $longitude;
    private \DateTimeImmutable $seenAt;

    private function __construct(int $driverId, float $latitude, float $longitude, \DateTimeImmutable $seenAt)
    {
        $this->driverId = $driverId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->seenAt = $seenAt;
    }

    public static function from(int $driverId, float $latitude, float $longitude, \DateTimeImmutable $seenAt): self
    {
        return new self($driverId, $latitude, $longitude, $seenAt);
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

    public function jsonSerialize(): array
    {
        return [
            'driverId' => $this->driverId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'seenAt' => $this->seenAt->format('Y-m-d H:i:s')
        ];
    }


}
