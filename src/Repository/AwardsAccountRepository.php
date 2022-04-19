<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Miles\AwardedMiles;
use LegacyFighter\Cabs\Entity\Miles\AwardsAccount;

class AwardsAccountRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByClientId(int $clientId): ?AwardsAccount
    {
        return $this->em->getRepository(AwardsAccount::class)->findOneBy(['clientId' => $clientId]);
    }

    public function save(AwardsAccount $account): void
    {
        $this->em->persist($account);
        $this->em->flush();
    }

    /**
     * @return AwardedMiles[]
     */
    public function findAllMilesBy(Client $client): array
    {
       return $this->findByClientId($client->getId())?->getMiles() ?? [];
    }
}
