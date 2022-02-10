<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\ClaimsResolver;

class ClaimsResolverRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByClientId(int $clientId): ?ClaimsResolver
    {
        return $this->em->getRepository(ClaimsResolver::class)->findOneBy(['clientId' => $clientId]);
    }

    public function save(ClaimsResolver $claimsResolver): ClaimsResolver
    {
        $this->em->persist($claimsResolver);
        return $claimsResolver;
    }
}
