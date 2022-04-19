<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\CarFleet\CarType;
use LegacyFighter\Cabs\CarFleet\CarTypeDTO;
use LegacyFighter\Cabs\CarFleet\CarTypeService;
use LegacyFighter\Cabs\Tests\Common\PrivateProperty;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CarTypeUpdateIntegrationTest extends KernelTestCase
{
    private CarTypeService $carTypeService;

    protected function setUp(): void
    {
        $this->carTypeService = $this->getContainer()->get(CarTypeService::class);
    }

    /**
     * @test
     */
    public function canCreateCarType(): void
    {
        //given
        $this->thereIsNoCarClassInTheSystem(CarType::CAR_CLASS_VAN);

        //when
        $created = $this->createCarClass('duże i dobre', CarType::CAR_CLASS_VAN);

        //then
        $loaded = $this->load($created->getId());
        self::assertEquals(CarType::CAR_CLASS_VAN, $loaded->getCarClass());
        self::assertEquals(0, $loaded->getCarsCounter());
        self::assertEquals(0, $loaded->getActiveCarsCounter());
        self::assertEquals('duże i dobre', $loaded->getDescription());
    }

    /**
     * @test
     */
    public function canChangeCarDescription(): void
    {
        //given
        $this->thereIsNoCarClassInTheSystem(CarType::CAR_CLASS_VAN);
        //and
        $this->createCarClass('duże i dobre', CarType::CAR_CLASS_VAN);

        //when
        $changed = $this->createCarClass('duże i bardzo dobre', CarType::CAR_CLASS_VAN);

        //then
        $loaded = $this->load($changed->getId());
        self::assertEquals(CarType::CAR_CLASS_VAN, $loaded->getCarClass());
        self::assertEquals(0, $loaded->getCarsCounter());
        self::assertEquals('duże i bardzo dobre', $loaded->getDescription());
    }

    /**
     * @test
     */
    public function canRegisterActiveCars(): void
    {
        //given
        $created = $this->createCarClass('dobre i duże', CarType::CAR_CLASS_VAN);
        //and
        $currentActiveCarsCount = $this->load($created->getId())->getActiveCarsCounter();

        //when
        $this->registerActiveCar(CarType::CAR_CLASS_VAN);

        //then
        $loaded = $this->load($created->getId());
        self::assertEquals($currentActiveCarsCount + 1, $loaded->getActiveCarsCounter());
    }

    /**
     * @test
     */
    public function canUnregisterActiveCars(): void
    {
        //given
        $created = $this->createCarClass('dobre i duże', CarType::CAR_CLASS_VAN);
        //and
        $this->registerActiveCar(CarType::CAR_CLASS_VAN);
        //and
        $currentActiveCarsCount = $this->load($created->getId())->getActiveCarsCounter();

        //when
        $this->unregisterActiveCar(CarType::CAR_CLASS_VAN);

        //then
        $loaded = $this->load($created->getId());
        self::assertEquals($currentActiveCarsCount - 1, $loaded->getActiveCarsCounter());
    }

    private function registerActiveCar(string $carClass): void
    {
        $this->carTypeService->registerActiveCar($carClass);
    }

    private function unregisterActiveCar(string $carClass): void
    {
        $this->carTypeService->unregisterActiveCar($carClass);
    }

    private function load(int $id): CarTypeDTO
    {
        return $this->carTypeService->loadDto($id);
    }

    private function createCarClass(string $desc, string $carClass): CarTypeDTO
    {
        $carType = new CarType($carClass, $desc, 1);
        PrivateProperty::setId(1, $carType);
        $carTypDTO = CarTypeDTO::new($carType);
        return $this->carTypeService->create($carTypDTO);
    }

    private function thereIsNoCarClassInTheSystem(string $carClass): void
    {
        $this->carTypeService->removeCarType($carClass);
    }
}
