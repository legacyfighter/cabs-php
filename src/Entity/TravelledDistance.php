<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use LegacyFighter\Cabs\Distance\Distance;
use Symfony\Component\Uid\Uuid;

#[Entity]
class TravelledDistance
{
    #[Id]
    #[Column(type:'uuid')]
    private Uuid $intervalId;

    #[Column(type: 'integer')]
    private int $driverId;

    #[Embedded(class: TimeSlot::class, columnPrefix: false)]
    private TimeSlot $timeSlot;

    #[Column(type: 'float')]
    private float $lastLatitude;

    #[Column(type: 'float')]
    private float $lastLongitude;

    #[Column(type: 'distance')]
    private Distance $distance;

    public function __construct(int $driverId, TimeSlot $timeSlot, DriverPosition $driverPosition)
    {
        $this->intervalId = Uuid::v4();
        $this->driverId = $driverId;
        $this->timeSlot = $timeSlot;
        $this->lastLatitude = $driverPosition->getLatitude();
        $this->lastLongitude = $driverPosition->getLongitude();
        $this->distance = Distance::zero();
    }

    public function containts(\DateTimeImmutable $timestamp): bool
    {
        return $this->timeSlot->contains($timestamp);
    }

    public function getLastLatitude(): float
    {
        return $this->lastLatitude;
    }

    public function getLastLongitude(): float
    {
        return $this->lastLongitude;
    }

    public function addDistance(Distance $distance, float $latitude, float $longitude): void
    {
        $this->distance = $distance->add($distance);
        $this->lastLatitude = $latitude;
        $this->lastLongitude = $longitude;
    }

    public function endsAt(\DateTimeImmutable $timestamp): bool
    {
        return $this->timeSlot->endsAt($timestamp);
    }

    public function isBefore(\DateTimeImmutable $timestamp): bool
    {
        return $this->timeSlot->isBefore($timestamp);
    }
}
