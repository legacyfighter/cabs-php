<?php

namespace LegacyFighter\Cabs\Crm\Claims;

use Doctrine\ORM\EntityManagerInterface;

class ClaimRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function count(): int
    {
        return $this->em->getRepository(Claim::class)->count([]);
    }

    public function getOne(int $claimId): ?Claim
    {
        return $this->em->find(Claim::class, $claimId);
    }

    public function save(Claim $claim): Claim
    {
        $this->em->persist($claim);
        $this->em->flush();
        return $claim;
    }

    public function countByOwnerId(int $clientId): int
    {
        return $this->em->getRepository(Claim::class)->count(['ownerId' => $clientId]);
    }
}
