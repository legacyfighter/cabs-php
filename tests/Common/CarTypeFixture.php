<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\CarFleet\CarType;
use LegacyFighter\Cabs\CarFleet\CarTypeDTO;
use LegacyFighter\Cabs\CarFleet\CarTypeService;

class CarTypeFixture
{
    public function __construct(
        private CarTypeService $carTypeService
    )
    {
    }

    public function anActiveCarCategory(string $carClass): CarTypeDTO
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
