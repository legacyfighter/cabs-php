<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\AwardedMiles;
use LegacyFighter\Cabs\Entity\Client;

class AwardedMilesRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(AwardedMiles $miles): void
    {
        $this->em->persist($miles);
        $this->em->flush();
    }

    /**
     * @return AwardedMiles[]
     */
    public function findAllByClient(Client $client): array
    {
        return $this->em->getRepository(AwardedMiles::class)->findBy(['client' => $client]);
    }
}
