<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Events;

use LegacyFighter\Cabs\Contracts\Model\Content\DocumentNumber;
use LegacyFighter\Cabs\Contracts\Model\ContentId;

abstract class DocumentEvent
{
    private int $documentId;
    private string $currentSate;
    private ContentId $contentId;
    private DocumentNumber $documentNumber;

    public function __construct(int $documentId, string $currentSate, ContentId $contentId, DocumentNumber $documentNumber)
    {
        $this->documentId = $documentId;
        $this->currentSate = $currentSate;
        $this->contentId = $contentId;
        $this->documentNumber = $documentNumber;
    }

    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    public function getCurrentSate(): string
    {
        return $this->currentSate;
    }

    public function getContentId(): ContentId
    {
        return $this->contentId;
    }

    public function getDocumentNumber(): DocumentNumber
    {
        return $this->documentNumber;
    }
}
