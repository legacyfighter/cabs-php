<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Claim;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Transit;

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

    /**
     * @return Claim[]
     */
    public function findByOwnerAndTransit(Client $owner, Transit $transit): array
    {
        return $this->em->getRepository(Claim::class)->findBy([
            'owner' => $owner,
            'transit' => $transit
        ]);
    }

    /**
     * @return Claim[]
     */
    public function findByOwner(Client $owner): array
    {
        return $this->em->getRepository(Claim::class)->findBy([
            'owner' => $owner,
        ]);
    }
}
