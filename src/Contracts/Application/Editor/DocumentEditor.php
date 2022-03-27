<?php

namespace LegacyFighter\Cabs\Contracts\Application\Editor;

use LegacyFighter\Cabs\Contracts\Model\Content\DocumentContent;
use LegacyFighter\Cabs\Contracts\Model\Content\DocumentContentRepository;
use Symfony\Component\Uid\Uuid;

class DocumentEditor
{
    public function __construct(
        private DocumentContentRepository $documentContentRepository
    )
    {
    }

    public function commit(DocumentDTO $document): CommitResult
    {
        $previousID = $document->getContentId();
        $content = new DocumentContent($previousID, $document->getContentVersion(), $document->getPhysicalContent());
        $this->documentContentRepository->save($content);
        return new CommitResult($content->getId(), CommitResult::SUCCESS);
    }

    public function get(Uuid $contentId): DocumentDTO
    {
        $content = $this->documentContentRepository->getOne($contentId);
        return new DocumentDTO($contentId, $content->getPhysicalContent(), $content->getContentVersion());
    }
}
