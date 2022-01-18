<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class DriverSession extends BaseEntity
{
    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $loggedAt;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $loggedOutAt = null;

    #[ManyToOne(targetEntity: Driver::class)]
    private Driver $driver;

    #[Column]
    private string $platesNumber;

    #[Column]
    private string $carClass;

    #[Column]
    private string $carBrand;

    public function getLoggedAt(): \DateTimeImmutable
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(\DateTimeImmutable $loggedAt): void
    {
        $this->loggedAt = $loggedAt;
    }

    public function getLoggedOutAt(): ?\DateTimeImmutable
    {
        return $this->loggedOutAt;
    }

    public function setLoggedOutAt(?\DateTimeImmutable $loggedOutAt): void
    {
        $this->loggedOutAt = $loggedOutAt;
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function setDriver(Driver $driver): void
    {
        $this->driver = $driver;
    }

    public function getPlatesNumber(): string
    {
        return $this->platesNumber;
    }

    public function setPlatesNumber(string $platesNumber): void
    {
        $this->platesNumber = $platesNumber;
    }

    public function getCarClass(): string
    {
        return $this->carClass;
    }

    public function setCarClass(string $carClass): void
    {
        $this->carClass = $carClass;
    }

    public function getCarBrand(): string
    {
        return $this->carBrand;
    }

    public function setCarBrand(string $carBrand): void
    {
        $this->carBrand = $carBrand;
    }
}
