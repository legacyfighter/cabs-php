<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\ContractAttachment;

class ContractAttachmentDTO implements \JsonSerializable
{
    private int $id;
    private int $contractId;
    private string $data;
    private \DateTimeImmutable $creationDate;
    private ?\DateTimeImmutable $acceptedAt;
    private ?\DateTimeImmutable $rejectedAt;
    private ?\DateTimeImmutable $changeDate;
    private string $status;

    private function __construct(ContractAttachment $attachment)
    {
        $this->id = $attachment->getId();
        $this->contractId = $attachment->getContract()->getId();
        $this->data = $attachment->getData();
        $this->creationDate = $attachment->getCreationDate();
        $this->acceptedAt = $attachment->getAcceptedAt();
        $this->rejectedAt = $attachment->getRejectedAt();
        $this->changeDate = $attachment->getChangeDate();
        $this->status = $attachment->getStatus();
    }

    public static function from(ContractAttachment $attachment): self
    {
        return new self($attachment);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContractId(): int
    {
        return $this->contractId;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getCreationDate(): \DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function getRejectedAt(): ?\DateTimeImmutable
    {
        return $this->rejectedAt;
    }

    public function getChangeDate(): ?\DateTimeImmutable
    {
        return $this->changeDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'creationDate' => $this->creationDate
        ];
    }


}
