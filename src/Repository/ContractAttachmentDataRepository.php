<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\ContractAttachmentData;
use Symfony\Component\Uid\Uuid;

class ContractAttachmentDataRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(ContractAttachmentData $contractAttachmentData): ContractAttachmentData
    {
        $this->em->persist($contractAttachmentData);
        $this->em->flush();
        return $contractAttachmentData;
    }

    /**
     * @param Uuid[] $attachmentIds
     * @return ContractAttachmentData[]
     */
    public function findByContractAttachmentNoIn(array $attachmentIds): array
    {
        return $this->em->getRepository(ContractAttachmentData::class)->matching(Criteria::create()->where(
            Criteria::expr()->in('contractAttachmentNo', $attachmentIds)
        ))->toArray();
    }

    public function deleteByAttachmentId(int $id): void
    {
        $this->em->getConnection()->executeQuery('
        DELETE FROM contract_attachment_data cad WHERE cad.contract_attachment_no = (
            (SELECT ca.contract_attachment_no FROM contract_attachment ca WHERE ca.id = :id)
        )', ['id' => $id]);
        $this->em->clear(ContractAttachmentData::class);
    }
}
