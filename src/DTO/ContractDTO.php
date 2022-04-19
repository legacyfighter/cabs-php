<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Agreements\Contract;
use LegacyFighter\Cabs\Entity\ContractAttachmentData;

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

    private function __construct()
    {

    }

    /**
     * @param ContractAttachmentData[] $attachments
     */
    public static function from(Contract $contract, array $attachments): self
    {
        $instance = new self();
        $instance->id = $contract->getId();
        $instance->subject = $contract->getSubject();
        $instance->partnerName = $contract->getPartnerName();
        $instance->creationDate = $contract->getCreationDate();
        $instance->acceptedAt = $contract->getAcceptedAt();
        $instance->rejectedAt = $contract->getRejectedAt();
        $instance->changeDate = $contract->getChangeDate();
        $instance->status = $contract->getStatus();
        $instance->contractNo = $contract->getContractNo();
        foreach ($attachments as $attachmentData) {
            $instance->attachments[] = ContractAttachmentDTO::from(
                $contract->findAttachment($attachmentData->getContractAttachmentNo()),
                $attachmentData
            );
        }
        return $instance;
    }

    public static function with(string $partnerName, string $subject): self
    {
        $instance = new self();
        $instance->partnerName = $partnerName;
        $instance->subject = $subject;
        return $instance;
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
