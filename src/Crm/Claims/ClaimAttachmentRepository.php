<?php

namespace LegacyFighter\Cabs\Crm\Claims;

use Doctrine\ORM\EntityManagerInterface;

class ClaimAttachmentRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(ClaimAttachment $claimAttachment): void
    {
        $this->em->persist($claimAttachment);
        $this->em->flush();
    }

    public function getOne(int $id): ?ClaimAttachment
    {
        return $this->em->find(ClaimAttachment::class, $id);
    }
}
