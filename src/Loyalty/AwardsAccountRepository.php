<?php

namespace LegacyFighter\Cabs\Loyalty;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Crm\Client;

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
