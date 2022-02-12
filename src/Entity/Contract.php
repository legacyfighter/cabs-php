<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class Contract extends BaseEntity
{
    public const STATUS_NEGOTIATIONS_IN_PROGRESS = 'negotiations-in-progress';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ACCEPTED = 'accepted';

    /**
     * @var Collection<ContractAttachment>
     */
    #[OneToMany(mappedBy: 'contract', targetEntity: ContractAttachment::class, cascade: ['all'])]
    private Collection $attachments;

    #[Column]
    private string $partnerName;

    #[Column]
    private string $subject;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $creationDate;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $acceptedAt = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $rejectedAt = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $changeDate = null;

    #[Column]
    private string $status = self::STATUS_NEGOTIATIONS_IN_PROGRESS;

    #[Column]
    private string $contractNo;

    public function __construct(string $partnerName, string $subject, string $contractNo)
    {
        $this->partnerName = $partnerName;
        $this->subject = $subject;
        $this->contractNo = $contractNo;
        $this->creationDate = new \DateTimeImmutable();
        $this->attachments = new ArrayCollection();
        $this->creationDate = new \DateTimeImmutable();
    }

    public function getAttachments(): array
    {
        return $this->attachments->toArray();
    }

    public function getPartnerName(): string
    {
        return $this->partnerName;
    }

    public function getSubject(): string
    {
        return $this->subject;
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

    public function proposeAttachment(string $data): ContractAttachment
    {
        $attachment = new ContractAttachment();
        $attachment->setData($data);
        $attachment->setContract($this);
        $this->attachments->add($attachment);
        return $attachment;
    }

    public function accept(): void
    {
        if($this->attachments->filter(fn(ContractAttachment $a) => $a->getStatus() !== ContractAttachment::STATUS_ACCEPTED_BY_BOTH_SIDES)->count() === 0) {
            $this->status = self::STATUS_ACCEPTED;
        } else {
            throw new \RuntimeException('Not all attachments accepted by both sides');
        }
    }

    public function reject(): void
    {
        $this->status = self::STATUS_REJECTED;
    }

    public function acceptAttachment(int $attachmentId): void
    {
        $attachment = $this->findAttachment($attachmentId);
        if(in_array($attachment->getStatus(), [ContractAttachment::STATUS_ACCEPTED_BY_ONE_SIDE, ContractAttachment::STATUS_ACCEPTED_BY_BOTH_SIDES], true)) {
            $attachment->setStatus(ContractAttachment::STATUS_ACCEPTED_BY_BOTH_SIDES);
        } else {
            $attachment->setStatus(ContractAttachment::STATUS_ACCEPTED_BY_ONE_SIDE);
        }
    }

    public function rejectAttachment(int $attachmentId): void
    {
        $this->findAttachment($attachmentId)->setStatus(ContractAttachment::STATUS_REJECTED);
    }

    private function findAttachment(int $attachmentId): ContractAttachment
    {
        return $this->attachments->filter(fn(ContractAttachment $a) => $a->getId() === $attachmentId)->first();
    }
}
