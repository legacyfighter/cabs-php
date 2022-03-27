<?php

namespace LegacyFighter\Cabs\Contracts\Model\Content;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class DocumentContentRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(DocumentContent $content): void
    {
        $this->em->persist($content);
        $this->em->flush();
    }

    public function getOne(Uuid $contentId): DocumentContent
    {
        $documentContent = $this->em->find(DocumentContent::class, $contentId);
        if($documentContent===null) {
            throw new \RuntimeException(sprintf('Document %s not found', $contentId));
        }

        return $documentContent;
    }
}
