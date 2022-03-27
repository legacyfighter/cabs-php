<?php

namespace LegacyFighter\Cabs\Contracts\Application\Editor;

use LegacyFighter\Cabs\Contracts\Model\Content\ContentVersion;
use Symfony\Component\Uid\Uuid;

class DocumentDTO
{
    private ?Uuid $contentId;
    private string $physicalContent;
    private ContentVersion $contentVersion;

    public function __construct(?Uuid $contentId, string $physicalContent, ContentVersion $contentVersion)
    {
        $this->contentId = $contentId;
        $this->physicalContent = $physicalContent;
        $this->contentVersion = $contentVersion;
    }

    public function getContentId(): ?Uuid
    {
        return $this->contentId;
    }

    public function getPhysicalContent(): string
    {
        return $this->physicalContent;
    }

    public function getContentVersion(): ContentVersion
    {
        return $this->contentVersion;
    }
}
