<?php

namespace LegacyFighter\Cabs\Repair\Api;

use LegacyFighter\Cabs\Money\Money;
use Symfony\Component\Uid\Uuid;

class ResolveResult
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    private ?Uuid $handlingParty;
    private ?Money $totalCost;
    private array $acceptedParts = [];
    private string $status;

    public function __construct(string $status, ?Uuid $handlingParty = null, ?Money $totalCost = null, array $acceptedParts = [])
    {
        $this->handlingParty = $handlingParty;
        $this->totalCost = $totalCost;
        $this->acceptedParts = $acceptedParts;
        $this->status = $status;
    }

    public function getHandlingParty(): ?Uuid
    {
        return $this->handlingParty;
    }

    public function getTotalCost(): ?Money
    {
        return $this->totalCost;
    }

    public function getAcceptedParts(): array
    {
        return $this->acceptedParts;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
