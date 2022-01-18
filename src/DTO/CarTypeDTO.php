<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\CarType;

class CarTypeDTO implements \JsonSerializable
{
    private int $id;
    private string $carClass;
    private string $status;
    private int $carsCounter;
    private string $description;
    private int $activeCarsCounter;
    private int $minNoOfCarsToActivateClass;

    private function __construct(CarType $carType)
    {
        $this->id = $carType->getId();
        $this->carClass = $carType->getCarClass();
        $this->status = $carType->getStatus();
        $this->carsCounter = $carType->getCarsCounter();
        $this->description = $carType->getDescription();
        $this->activeCarsCounter = $carType->getActiveCarsCounter();
        $this->minNoOfCarsToActivateClass = $carType->getMinNoOfCarsToActivateClass();
    }

    public static function new(CarType $carType): self
    {
        return new self($carType);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCarClass(): int|string
    {
        return $this->carClass;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCarsCounter(): int
    {
        return $this->carsCounter;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getActiveCarsCounter(): int
    {
        return $this->activeCarsCounter;
    }

    public function getMinNoOfCarsToActivateClass(): int
    {
        return $this->minNoOfCarsToActivateClass;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'carClass' => $this->carClass,
            'status' => $this->status,
            'carsCounter' => $this->carsCounter,
            'description' => $this->description,
            'activeCarsCounter' => $this->activeCarsCounter,
            'minNoOfCarsToActivateClass' => $this->minNoOfCarsToActivateClass
        ];
    }
}
