<?php

namespace LegacyFighter\Cabs\Crm\Claims;

use Doctrine\ORM\EntityManagerInterface;

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
