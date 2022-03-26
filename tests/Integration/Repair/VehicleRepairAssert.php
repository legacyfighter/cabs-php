<?php

namespace LegacyFighter\Cabs\Tests\Integration\Repair;

use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Party\Api\PartyId;
use LegacyFighter\Cabs\Repair\Api\ResolveResult;
use PHPUnit\Framework\Assert;

class VehicleRepairAssert extends Assert
{
    private ResolveResult $result;

    public function __construct(ResolveResult $result, bool $demandSuccess = true)
    {
        if($demandSuccess) {
            self::assertEquals(ResolveResult::STATUS_SUCCESS, $result->getStatus());
        } else {
            self::assertEquals(ResolveResult::STATUS_ERROR, $result->getStatus());
        }
        $this->result = $result;
    }

    public function free(): self
    {
        self::assertEquals(Money::zero(), $this->result->getTotalCost());
        return $this;
    }

    public function allParts(array $parts): self
    {
        self::assertEquals($parts, $this->result->getAcceptedParts());
        return $this;
    }

    public function by(PartyId $handlingParty): self
    {
        self::assertEquals($handlingParty->toUuid(), $this->result->getHandlingParty());
        return $this;
    }

    public function allPartsBut(array $parts, array $excludedParts): self
    {
        $exptectedParts = array_filter($parts, fn(string $part) => !in_array($part, $excludedParts));
        self::assertEquals($exptectedParts, $this->result->getAcceptedParts());
        return $this;
    }

}
