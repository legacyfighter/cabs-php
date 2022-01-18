<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\AwardsAccount;
use LegacyFighter\Cabs\Entity\Client;

class AwardsAccountRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByClient(Client $client): ?AwardsAccount
    {
        return $this->em->getRepository(AwardsAccount::class)->findOneBy(['client' => $client]);
    }

    public function save(AwardsAccount $account): void
    {
        $this->em->persist($account);
        $this->em->flush();
    }
}
