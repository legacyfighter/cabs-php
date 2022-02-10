<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\Claim;

class ClaimDTO implements \JsonSerializable
{
    private int $claimId;
    private int $clientId;
    private int $transitId;
    private string $reason;
    private ?string $incidentDescription = null;
    private bool $isDraft = true;
    private \DateTimeImmutable $creationDate;
    private ?\DateTimeImmutable $completionDate = null;
    private ?\DateTimeImmutable $changeDate = null;
    private ?string $completionMode;
    private string $status;
    private string $claimNo;

    private function __construct()
    {

    }

    public static function from(Claim $claim): self
    {
        $dto = new self();
        if($claim->getStatus() === Claim::STATUS_DRAFT) {
            $dto->isDraft = true;
        } else {
            $dto->isDraft = false;
        }
        $dto->claimId = $claim->getId();
        $dto->reason = $claim->getReason();
        $dto->incidentDescription = $claim->getIncidentDescription();
        $dto->transitId = $claim->getTransit()->getId();
        $dto->clientId = $claim->getOwner()->getId();
        $dto->completionDate = $claim->getCompletionDate();
        $dto->changeDate = $claim->getChangeDate();
        $dto->claimNo = $claim->getClaimNo();
        $dto->status = $claim->getStatus();
        $dto->completionMode = $claim->getCompletionMode();
        $dto->creationDate = $claim->getCreationDate();

        return  $dto;
    }

    public static function with(string $desc, string $reason, int $clientId, int $transitId): self
    {
        $dto = new self();
        $dto->incidentDescription = $desc;
        $dto->reason = $reason;
        $dto->clientId = $clientId;
        $dto->transitId = $transitId;

        return $dto;
    }

    public function getClaimId(): int
    {
        return $this->claimId;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getTransitId(): int
    {
        return $this->transitId;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getIncidentDescription(): ?string
    {
        return $this->incidentDescription;
    }

    public function isDraft(): bool
    {
        return $this->isDraft;
    }

    public function getCreationDate(): \DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function getCompletionDate(): ?\DateTimeImmutable
    {
        return $this->completionDate;
    }

    public function getChangeDate(): ?\DateTimeImmutable
    {
        return $this->changeDate;
    }

    public function getCompletionMode(): ?string
    {
        return $this->completionMode;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getClaimNo(): string
    {
        return $this->claimNo;
    }

    public function setIsDraft(bool $isDraft): void
    {
        $this->isDraft = $isDraft;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->claimId,
            'clientId' => $this->clientId,
            'transitId' => $this->transitId,
            'reason' => $this->reason,
            'status' => $this->status,
            'completionMode' => $this->completionMode,
            'claimNo' => $this->claimNo
        ];
    }
}
