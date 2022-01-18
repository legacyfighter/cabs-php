<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\CarType;

class CarTypeRepository
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getOne(int $carTypeId): ?CarType
    {
        return $this->em->find(CarType::class, $carTypeId);
    }

    public function findByCarClass(string $carClass): ?CarType
    {
        return $this->em->getRepository(CarType::class)->findOneBy(['carClass' => $carClass]);
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
