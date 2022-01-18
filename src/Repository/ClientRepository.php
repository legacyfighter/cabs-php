<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Client;

class ClientRepository
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getOne(int $clientId): ?Client
    {
        return $this->em->find(Client::class, $clientId);
    }

    public function save(Client $client): Client
    {
        $this->em->persist($client);
        $this->em->flush();
        return $client;
    }
}
