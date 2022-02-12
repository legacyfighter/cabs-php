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

    private function __construct()
    {

    }

    public static function from(ContractAttachment $attachment): self
    {
        $instance = new self();
        $instance->id = $attachment->getId();
        $instance->contractId = $attachment->getContract()->getId();
        $instance->data = $attachment->getData();
        $instance->creationDate = $attachment->getCreationDate();
        $instance->acceptedAt = $attachment->getAcceptedAt();
        $instance->rejectedAt = $attachment->getRejectedAt();
        $instance->changeDate = $attachment->getChangeDate();
        $instance->status = $attachment->getStatus();
        return $instance;
    }

    public static function with(string $data): self
    {
        $instance = new self();
        $instance->data = $data;
        return $instance;
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
