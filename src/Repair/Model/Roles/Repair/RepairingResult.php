<?php

namespace LegacyFighter\Cabs\Repair\Model\Roles\Repair;

use LegacyFighter\Cabs\Money\Money;
use Symfony\Component\Uid\Uuid;

class RepairingResult
{
    private Uuid $handlingParty;
    private Money $totalCost;
    private array $handledParts;

    public function __construct(Uuid $handlingParty, Money $totalCost, array $handledParts)
    {
        $this->handlingParty = $handlingParty;
        $this->totalCost = $totalCost;
        $this->handledParts = $handledParts;
    }

    public function getHandlingParty(): Uuid
    {
        return $this->handlingParty;
    }

    public function getTotalCost(): Money
    {
        return $this->totalCost;
    }

    public function getHandledParts(): array
    {
        return $this->handledParts;
    }
}
