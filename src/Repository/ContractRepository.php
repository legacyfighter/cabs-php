<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Contract;

class ContractRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @return Contract[]
     */
    public function findByPartnerName(string $partnerName): array
    {
        return $this->em->getRepository(Contract::class)->findBy(['partnerName' => $partnerName]);
    }

    public function save(Contract $contract): Contract
    {
        $this->em->persist($contract);
        $this->em->flush();
        return $contract;
    }

    public function getOne(int $id): ?Contract
    {
        return $this->em->find(Contract::class, $id);
    }
}
