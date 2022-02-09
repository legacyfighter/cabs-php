<?php

namespace LegacyFighter\Cabs\Entity;

class CarTypeActiveCounter
{
    private CarType $carType;

    public function __construct(CarType $carType)
    {
        $this->carType = $carType;
    }

    public function registerActiveCar(): void
    {
        $this->carType->registerActiveCar();
    }

    public function unregisterActiveCar(): void
    {
        $this->carType->unregisterActiveCar();
    }

    public function getActiveCarsCounter(): int
    {
        return $this->carType->getActiveCarsCounter();
    }
}
