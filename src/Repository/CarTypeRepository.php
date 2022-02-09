<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\CarType;
use LegacyFighter\Cabs\Entity\CarTypeActiveCounter;

class CarTypeRepository
{
    public function __construct(
        private EntityManagerInterface $em,
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
        $carType = $this->findByCarClass($carClass);
        if($carType === null) {
            return null;
        }

        return new CarTypeActiveCounter($carType);
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
        $this->em->flush();
    }

    public function delete(CarType $carType): void
    {
        $this->em->remove($carType);
        $this->em->flush();
    }
}
