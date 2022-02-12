<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;
use Symfony\Component\Uid\Uuid;

#[Entity]
class ContractAttachmentData extends BaseEntity
{
    #[Column(type: 'uuid', nullable: false)]
    private Uuid $contractAttachmentNo;

    #[Column(type: 'text')]
    private string $data;

    #[Column(type: 'datetime_immutable', nullable: false)]
    private \DateTimeImmutable $creationDate;

    public function __construct(Uuid $contractAttachmentId, string $data)
    {
        $this->contractAttachmentNo = $contractAttachmentId;
        $this->data = $data;
        $this->creationDate = new \DateTimeImmutable();
    }

    public function getContractAttachmentNo(): Uuid
    {
        return $this->contractAttachmentNo;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getCreationDate(): \DateTimeImmutable
    {
        return $this->creationDate;
    }
}
