<?php

namespace LegacyFighter\Cabs\Ride;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class TransitDemandRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByRequestUuid(Uuid $requestUuid): ?TransitDemand
    {
        return $this->em->getRepository(TransitDemand::class)->findOneBy([
            'requestUuid' => $requestUuid
        ]);
    }

    public function save(TransitDemand $transitDemand): TransitDemand
    {
        $this->em->persist($transitDemand);
        $this->em->flush();
        return $transitDemand;
    }
}
