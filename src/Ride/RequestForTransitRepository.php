<?php

namespace LegacyFighter\Cabs\Ride;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class RequestForTransitRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getOne(int $id): ?RequestForTransit
    {
        return $this->em->find(RequestForTransit::class, $id);
    }

    public function findByRequestUuid(Uuid $requestUuid): ?RequestForTransit
    {
        return $this->em->getRepository(RequestForTransit::class)->findOneBy([
            'requestUuid' => $requestUuid
        ]);
    }

    public function save(RequestForTransit $requestForTransit): RequestForTransit
    {
        $this->em->persist($requestForTransit);
        $this->em->flush();
        return $requestForTransit;
    }
}
