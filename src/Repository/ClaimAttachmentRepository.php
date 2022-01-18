<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\ClaimAttachment;

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
