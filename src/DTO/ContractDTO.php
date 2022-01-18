<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\Contract;

class ContractDTO implements \JsonSerializable
{
    private int $id;
    private string $subject;
    private string $partnerName;
    private \DateTimeImmutable $creationDate;
    private ?\DateTimeImmutable $acceptedAt;
    private ?\DateTimeImmutable $rejectedAt;
    private ?\DateTimeImmutable $changeDate;
    private string $status;
    private string $contractNo;
    /**
     * @var ContractAttachmentDTO[]
     */
    private array $attachments = [];

    private function __construct(Contract $contract)
    {
        $this->id = $contract->getId();
        $this->subject = $contract->getSubject();
        $this->partnerName = $contract->getPartnerName();
        $this->creationDate = $contract->getCreationDate();
        $this->acceptedAt = $contract->getAcceptedAt();
        $this->rejectedAt = $contract->getRejectedAt();
        $this->changeDate = $contract->getChangeDate();
        $this->status = $contract->getStatus();
        $this->contractNo = $contract->getContractNo();
        foreach ($contract->getAttachments() as $attachment) {
            $this->attachments[] = ContractAttachmentDTO::from($attachment);
        }
    }

    public static function from(Contract $contract): self
    {
        return new self($contract);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getPartnerName(): string
    {
        return $this->partnerName;
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

    public function getContractNo(): string
    {
        return $this->contractNo;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'partnerName' => $this->partnerName,
            'status' => $this->status,
            'contractNo' => $this->contractNo,
            'attachments' => $this->attachments,
            'creationDate' => $this->creationDate
        ];
    }


}
