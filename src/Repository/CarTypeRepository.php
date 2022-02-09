<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\CarType;
use LegacyFighter\Cabs\Entity\CarTypeActiveCounter;

class CarTypeRepository
{


    public function __construct(
        private EntityManagerInterface $em,
        private CarTypeActiveCounterRepository $carTypeActiveCounterRepository
    ) {}


    public function getOne(int $carTypeId): ?CarType
    {
        return $this->em->find(CarType::class, $carTypeId);
    }

    public function findByCarClass(string $carClass): ?CarType
    {
        return $this->em->getRepository(CarType::class)->findOneBy(['carClass' => $carClass]);
    }

    public function findActiveCounter(string $carClass): ?CarTypeActiveCounter
    {
        return $this->carTypeActiveCounterRepository->findByCarClass($carClass);
    }

    /**
     * @return CarType[]
     */
    public function findByStatus(string $status): array
    {
        return $this->em->getRepository(CarType::class)->findBy(['status' => $status]);
    }

    public function save(CarType $carType): void
    {
        $this->em->persist($carType);
        $this->carTypeActiveCounterRepository->save(new CarTypeActiveCounter($carType->getCarClass()));
        $this->em->flush();
    }

    public function delete(CarType $carType): void
    {
        $this->em->remove($carType);
        $this->carTypeActiveCounterRepository->delete($this->carTypeActiveCounterRepository->findByCarClass($carType->getCarClass()));
        $this->em->flush();
    }

    public function incrementCounter(string $carClass): void
    {
        $this->carTypeActiveCounterRepository->incrementCounter($carClass);
    }

    public function decrementCounter(string $carClass): void
    {
        $this->carTypeActiveCounterRepository->decrementCounter($carClass);
    }
}
