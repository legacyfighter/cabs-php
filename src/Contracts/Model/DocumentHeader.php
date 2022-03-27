<?php

namespace LegacyFighter\Cabs\Contracts\Model;

use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Contracts\Model\Content\DocumentNumber;

#[Entity]
class DocumentHeader extends BaseEntity
{
    private DocumentNumber $number;
    private int $authorId;
    private ?int $verifierId = null;
    private ?string $stateDescriptor = null;
    private ?ContentId $contentId = null;

    public function __construct(int $authorId, DocumentNumber $number)
    {
        $this->authorId = $authorId;
        $this->number = $number;
    }

    public function changeCurrentContent(ContentId $contentId): void
    {
        $this->contentId = $contentId;
    }

    public function noEmpty(): bool
    {
        return $this->contentId !== null;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $authorId): void
    {
        $this->authorId = $authorId;
    }

    public function getVerifierId(): ?int
    {
        return $this->verifierId;
    }

    public function setVerifierId(?int $verifierId): void
    {
        $this->verifierId = $verifierId;
    }

    public function getStateDescriptor(): ?string
    {
        return $this->stateDescriptor;
    }

    public function setStateDescriptor(?string $stateDescriptor): void
    {
        $this->stateDescriptor = $stateDescriptor;
    }

    public function getDocumentNumber(): DocumentNumber
    {
        return $this->number;
    }

    public function getContentId(): ?ContentId
    {
        return $this->contentId;
    }
}
