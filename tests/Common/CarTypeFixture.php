<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\DTO\CarTypeDTO;
use LegacyFighter\Cabs\Entity\CarType;
use LegacyFighter\Cabs\Service\CarTypeService;

class CarTypeFixture
{
    public function __construct(
        private CarTypeService $carTypeService
    )
    {
    }

    public function anActiveCarCategory(string $carClass): CarType
    {
        $carType = new CarType($carClass, 'opis', 1);
        PrivateProperty::setId(1, $carType);
        $carTypeDTO = CarTypeDTO::new($carType);
        $carType = $this->carTypeService->create($carTypeDTO);
        $this->carTypeService->registerCar($carClass);
        $this->carTypeService->activate($carType->getId());
        return $carType;
    }
}
