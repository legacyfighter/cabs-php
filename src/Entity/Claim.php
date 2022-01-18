<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use LegacyFighter\Cabs\Common\BaseEntity;

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

    #[ManyToOne]
    private Client $owner;

    #[OneToOne]
    private Transit $transit;

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

    public function __construct()
    {
    }

    public function getOwner(): Client
    {
        return $this->owner;
    }

    public function setOwner(Client $owner): void
    {
        $this->owner = $owner;
    }

    public function getTransit(): Transit
    {
        return $this->transit;
    }

    public function setTransit(Transit $transit): void
    {
        $this->transit = $transit;
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
        if(!in_array($status, [self::STATUS_NEW, self::STATUS_DRAFT, self::STATUS_ESCALATED, self::STATUS_IN_PROCESS, self::STATUS_REFUNDED, self::STATUS_REJECTED])) {
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
}
