<?php

namespace LegacyFighter\Cabs\Contracts\Infra;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\DocumentHeaderRepository;

//LockModeType surprised you? MUST see: https://youtu.be/uj25PbkHb94?t=499

class DoctrineDocumentHeaderRepository implements DocumentHeaderRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(DocumentHeader $header): void
    {
        if($this->em->contains($header)) {
            $this->em->lock($header, LockMode::OPTIMISTIC);
        } else {
            $this->em->persist($header);
        }
        $this->em->flush();
    }

    public function getOne(int $id): ?DocumentHeader
    {
        return $this->em->find(DocumentHeader::class, $id, LockMode::OPTIMISTIC);
    }
}
