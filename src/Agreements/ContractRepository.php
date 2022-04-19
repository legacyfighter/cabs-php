<?php

namespace LegacyFighter\Cabs\Agreements;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

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

    public function findByAttachmentId(int $attachmentId): ?Contract
    {
        return $this->em->createQuery(sprintf(
            'SELECT c FROM %s c JOIN c.attachments ca WHERE ca.id = %s',
            Contract::class,
            $attachmentId
        ))->getSingleResult();
    }

    public function findContractAttachmentNoById(int $attachmentId): ?Uuid
    {
        $uuid = $this->em->getConnection()->fetchOne(
            'SELECT c.contract_attachment_no FROM contract_attachment c WHERE c.id = :id',
            ['id' => $attachmentId]
        );
        return $uuid !== false ? Uuid::fromString($uuid) : null;
    }
}
