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
    #[OneToMany(mappedBy: 'contract', targetEntity: ContractAttachment::class)]
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

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
        $this->creationDate = new \DateTimeImmutable();
    }

    public function getAttachments(): array
    {
        return $this->attachments->toArray();
    }

    public function setAttachments(array $attachments): void
    {
        $this->attachments = new ArrayCollection($attachments);
    }

    public function getPartnerName(): string
    {
        return $this->partnerName;
    }

    public function setPartnerName(string $partnerName): void
    {
        $this->partnerName = $partnerName;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getCreationDate(): \DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(?\DateTimeImmutable $acceptedAt): void
    {
        $this->acceptedAt = $acceptedAt;
    }

    public function getRejectedAt(): ?\DateTimeImmutable
    {
        return $this->rejectedAt;
    }

    public function setRejectedAt(?\DateTimeImmutable $rejectedAt): void
    {
        $this->rejectedAt = $rejectedAt;
    }

    public function getChangeDate(): ?\DateTimeImmutable
    {
        return $this->changeDate;
    }

    public function setChangeDate(?\DateTimeImmutable $changeDate): void
    {
        $this->changeDate = $changeDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getContractNo(): string
    {
        return $this->contractNo;
    }

    public function setContractNo(string $contractNo): void
    {
        $this->contractNo = $contractNo;
    }
}
