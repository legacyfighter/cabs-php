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

    private function __construct(\DateTimeImmutable $loggedAt, ?\DateTimeImmutable $loggedOutAt, string $platesNumber, string $carClass, string $carBrand)
    {
        $this->loggedAt = $loggedAt;
        $this->loggedOutAt = $loggedOutAt;
        $this->platesNumber = $platesNumber;
        $this->carClass = $carClass;
        $this->carBrand = $carBrand;
    }

    public static function with(\DateTimeImmutable $loggedAt, ?\DateTimeImmutable $loggedOutAt, string $platesNumber, string $carClass, string $carBrand): self
    {
        return new self($loggedAt, $loggedOutAt, $platesNumber, $carClass, $carBrand);
    }

    public static function from(DriverSession $session): self
    {
        return new self(
            $session->getLoggedAt(),
            $session->getLoggedOutAt(),
            $session->getPlatesNumber(),
            $session->getCarClass(),
            $session->getCarBrand()
        );
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
