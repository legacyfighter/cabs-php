<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\Config\AppProperties;
use LegacyFighter\Cabs\DTO\CarTypeDTO;
use LegacyFighter\Cabs\Entity\CarType;
use LegacyFighter\Cabs\Repository\CarTypeRepository;

class CarTypeService
{
    private CarTypeRepository $carTypeRepository;
    private AppProperties $appProperties;

    public function __construct(CarTypeRepository $carTypeRepository, AppProperties $appProperties)
    {
        $this->carTypeRepository = $carTypeRepository;
        $this->appProperties = $appProperties;
    }

    public function load(int $id): CarType
    {
        $carType = $this->carTypeRepository->getOne($id);
        if($carType === null) {
            throw new \InvalidArgumentException('Cannot find car type');
        }

        return $carType;
    }

    public function loadDto(int $id): CarTypeDTO
    {
        return CarTypeDTO::new($this->load($id));
    }

    public function create(CarTypeDTO $carTypeDTO): CarType
    {
        $byCarClass = $this->carTypeRepository->findByCarClass($carTypeDTO->getCarClass());
        if($byCarClass === null) {
            $type = new CarType($carTypeDTO->getCarClass(), $carTypeDTO->getDescription(), $carTypeDTO->getMinNoOfCarsToActivateClass());
            $this->carTypeRepository->save($type);
            return $type;
        } else {
            $byCarClass->setDescription($carTypeDTO->getDescription());
            return $byCarClass;
        }
    }

    public function activate(int $id): void
    {
        $carType = $this->load($id);
        $carType->activate();
    }

    public function deactivate(int $id): void
    {
        $carType = $this->load($id);
        $carType->deactivate();
    }

    public function registerCar(string $carClass): void
    {
        $carType = $this->findByCarClass($carClass);
        $carType->registerCar();
    }

    public function unregisterCar(string $carClass): void
    {
        $carType = $this->findByCarClass($carClass);
        $carType->unregisterCar();
    }

    public function unregisterActiveCar(string $carClass): void
    {
        $carType = $this->findByCarClass($carClass);
        $carType->unregisterActiveCar();
    }

    public function registerActiveCar(string $carClass): void
    {
        $carType = $this->findByCarClass($carClass);
        $carType->registerActiveCar();
    }

    /**
     * @return string[]
     */
    public function findActiveCarClasses(): array
    {
        return array_map(fn(CarType $carType) => $carType->getCarClass(), $this->carTypeRepository->findByStatus(CarType::STATUS_ACTIVE));
    }

    public function getMinNumberOfCars(string $carClass): int
    {
        if($carClass === CarType::CAR_CLASS_ECO) {
            return $this->appProperties->getMinNoOfCarsForEcoClass();
        } else {
            return 10;
        }
    }

    public function removeCarType(string $carClass): void
    {
        $carType = $this->carTypeRepository->findByCarClass($carClass);
        if($carType !== null) {
            $this->carTypeRepository->delete($carType);
        }
    }

    private function findByCarClass(string $carClass): CarType
    {
        $byCarClass = $this->carTypeRepository->findByCarClass($carClass);
        if($byCarClass === null) {
            throw new \InvalidArgumentException('Car class does not exist: '.$carClass);
        }
        return $byCarClass;
    }
}
