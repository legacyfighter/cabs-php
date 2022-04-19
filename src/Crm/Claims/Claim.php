<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Crm\Claims;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Money\Money;

#[Entity]
class Claim extends BaseEntity
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROCESS = 'in-process';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_ESCALATED = 'escalated';
    public const STATUS_REJECTED = 'rejected';

    public const COMPLETION_MODE_MANUAL = 'manual';
    public const COMPLETION_MODE_AUTOMATIC = 'automatic';

    public const ALL_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_NEW,
        self::STATUS_IN_PROCESS,
        self::STATUS_REFUNDED,
        self::STATUS_ESCALATED,
        self::STATUS_REJECTED
    ];

    #[Column(type: 'integer')]
    private int $ownerId;

    #[Column(type: 'integer')]
    private int $transitId;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $creationDate;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completionDate = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $changeDate = null;

    #[Column(type: 'string')]
    private string $reason;

    #[Column(type: 'string', nullable: true)]
    private ?string $incidentDescription = null;

    #[Column(type: 'string', nullable: true)]
    private ?string $completionMode = null;

    #[Column(type: 'string')]
    private string $status;

    #[Column(type: 'string')]
    private string $claimNo;

    #[Column(type: 'money', nullable: true)]
    private ?Money $transitPrice = null;

    public function __construct()
    {
    }

    public function escalate(): void
    {
        $this->setStatus(self::STATUS_ESCALATED);
        $this->setCompletionDate(new \DateTimeImmutable());
        $this->setChangeDate(new \DateTimeImmutable());
        $this->setCompletionMode(self::COMPLETION_MODE_MANUAL);
    }

    public function refund(): void
    {
        $this->setStatus(self::STATUS_REFUNDED);
        $this->setCompletionDate(new \DateTimeImmutable());
        $this->setChangeDate(new \DateTimeImmutable());
        $this->setCompletionMode(self::COMPLETION_MODE_AUTOMATIC);
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function setOwnerId(int $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    public function getCreationDate(): \DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getCompletionDate(): ?\DateTimeImmutable
    {
        return $this->completionDate;
    }

    public function setCompletionDate(?\DateTimeImmutable $completionDate): void
    {
        $this->completionDate = $completionDate;
    }

    public function getChangeDate(): ?\DateTimeImmutable
    {
        return $this->changeDate;
    }

    public function setChangeDate(?\DateTimeImmutable $changeDate): void
    {
        $this->changeDate = $changeDate;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    public function getIncidentDescription(): ?string
    {
        return $this->incidentDescription;
    }

    public function setIncidentDescription(?string $incidentDescription): void
    {
        $this->incidentDescription = $incidentDescription;
    }

    public function getCompletionMode(): ?string
    {
        return $this->completionMode;
    }

    public function setCompletionMode(string $completionMode): void
    {
        $this->completionMode = $completionMode;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        if(!in_array($status, self::ALL_STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid status provided');
        }
        $this->status = $status;
    }

    public function getClaimNo(): string
    {
        return $this->claimNo;
    }

    public function setClaimNo(string $claimNo): void
    {
        $this->claimNo = $claimNo;
    }

    public function getTransitId(): int
    {
        return $this->transitId;
    }

    public function getTransitPrice(): Money
    {
        return $this->transitPrice ?? Money::zero();
    }

    public function setTransitId(int $transitId): void
    {
        $this->transitId = $transitId;
    }

    public function setTransitPrice(Money $transitPrice): void
    {
        $this->transitPrice = $transitPrice;
    }
}
