<?php

namespace LegacyFighter\Cabs\Contracts\Model\Content;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Uid\Uuid;

#[Entity]
class DocumentContent
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    public function __construct(
        #[Column(type: 'uuid', nullable: true)]
        private ?Uuid $previousId,
        #[Embedded(class: ContentVersion::class, columnPrefix: false)]
        private ContentVersion $contentVersion, //just a human readable descriptor
        #[Column]
        private string $physicalContent //some kind of reference to file, version control. In sour sample i will be a blob stored in DB:)
    )
    {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPreviousId(): ?Uuid
    {
        return $this->previousId;
    }

    public function getContentVersion(): ContentVersion
    {
        return $this->contentVersion;
    }

    public function getPhysicalContent(): string
    {
        return $this->physicalContent;
    }
}
