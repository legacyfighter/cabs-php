<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class CarType extends BaseEntity
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const CAR_CLASS_ECO = 'eco';
    public const CAR_CLASS_VAN = 'van';
    public const CAR_CLASS_REGULAR = 'regular';
    public const CAR_CLASS_PREMIUM = 'premium';

    #[Column]
    private string $carClass;

    #[Column(type: 'string')]
    private string $description;

    #[Column(type: 'string')]
    private string $status = self::STATUS_INACTIVE;

    #[Column(type: 'integer')]
    private int $carsCounter = 0;

    #[Column(type: 'integer')]
    private int $minNoOfCarsToActivateClass;

    #[Column(type: 'integer')]
    private int $activeCarsCounter = 0;

    public function __construct(string $carClass, string $description, int $minNoOfCarsToActivateClass)
    {
        $this->carClass = $carClass;
        $this->description = $description;
        $this->minNoOfCarsToActivateClass = $minNoOfCarsToActivateClass;
    }

    public function registerActiveCar(): void
    {
        $this->activeCarsCounter++;
    }

    public function unregisterActiveCar(): void
    {
        $this->activeCarsCounter--;
    }

    public function registerCar(): void
    {
        $this->carsCounter++;
    }

    public function unregisterCar(): void
    {
        $this->carsCounter--;
        if($this->carsCounter < 0) {
            throw new \RuntimeException();
        }
    }

    public function activate(): void
    {
        if($this->carsCounter < $this->minNoOfCarsToActivateClass) {
            throw new \RuntimeException('Cannot activate car class when less than ' . $this->minNoOfCarsToActivateClass . ' cars in the fleet');
        }
        $this->status = self::STATUS_ACTIVE;
    }

    public function deactivate(): void
    {
        $this->status = self::STATUS_INACTIVE;
    }

    public function getCarClass(): string
    {
        return $this->carClass;
    }

    public function setCarClass(string $carClass): void
    {
        if(!in_array($carClass, [self::CAR_CLASS_ECO, self::CAR_CLASS_PREMIUM, self::CAR_CLASS_REGULAR, self::CAR_CLASS_VAN])) {
            throw new \InvalidArgumentException('Invalid car class value');
        }
        $this->carClass = $carClass;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCarsCounter(): int
    {
        return $this->carsCounter;
    }

    public function getMinNoOfCarsToActivateClass(): int
    {
        return $this->minNoOfCarsToActivateClass;
    }

    public function getActiveCarsCounter(): int
    {
        return $this->activeCarsCounter;
    }
}
