<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class ClaimAttachment extends BaseEntity
{
    #[ManyToOne(targetEntity: Claim::class)]
    private Claim $claim;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $creationDate;

    #[Column]
    private string $description;

    #[Column(type: 'text')]
    private string $data;

    public function __construct()
    {
        $this->creationDate = new \DateTimeImmutable();
    }

    public function getClient(): Client
    {
        return $this->claim->getOwner();
    }

    public function getClaim(): Claim
    {
        return $this->claim;
    }

    public function getCreationDate(): \DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function setClaim(Claim $claim): void
    {
        $this->claim = $claim;
    }
}
