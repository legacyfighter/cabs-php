<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\DriverSession;

class DriverSessionDTO implements \JsonSerializable
{
    private \DateTimeImmutable $loggedAt;
    private ?\DateTimeImmutable $loggedOutAt;
    private string $platesNumber;
    private string $carClass;
    private string $carBrand;

    private function __construct(DriverSession $session)
    {
        $this->loggedAt = $session->getLoggedAt();
        $this->loggedOutAt = $session->getLoggedOutAt();
        $this->platesNumber = $session->getPlatesNumber();
        $this->carClass = $session->getCarClass();
        $this->carBrand = $session->getCarBrand();
    }

    public static function from(DriverSession $session): self
    {
        return new self($session);
    }

    public function getLoggedAt(): \DateTimeImmutable
    {
        return $this->loggedAt;
    }

    public function getLoggedOutAt(): ?\DateTimeImmutable
    {
        return $this->loggedOutAt;
    }

    public function getPlatesNumber(): string
    {
        return $this->platesNumber;
    }

    public function getCarClass(): string
    {
        return $this->carClass;
    }

    public function getCarBrand(): string
    {
        return $this->carBrand;
    }

    public function jsonSerialize(): array
    {
        return [
            'loggedAt' => $this->loggedAt->format('Y-m-d H:i:s'),
            'loggedOutAt' => $this->loggedOutAt?->format('Y-m-d H:i:s'),
            'platesNumber' => $this->platesNumber,
            'carClass' => $this->carClass,
            'carBrand' => $this->carBrand
        ];
    }
}
