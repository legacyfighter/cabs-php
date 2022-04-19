<?php

namespace LegacyFighter\Cabs\Loyalty;

class TwoStepExpiringMiles implements Miles
{
    private int $amount;
    private \DateTimeImmutable $whenFirstHalfExpires;
    private \DateTimeImmutable $whenExpires;

    public function __construct(int $amount, \DateTimeImmutable $whenFirstHalfExpires, \DateTimeImmutable $whenExpires)
    {
        $this->amount = $amount;
        $this->whenFirstHalfExpires = $whenFirstHalfExpires;
        $this->whenExpires = $whenExpires;
    }

    public function getAmountFor(\DateTimeImmutable $moment): int
    {
        if($this->whenFirstHalfExpires >= $moment) {
            return $this->amount;
        }

        if($this->whenExpires >= $moment) {
            return $this->amount - $this->halfOf($this->amount);
        }

        return 0;
    }

    private function halfOf(int $amount): int
    {
        return $amount / 2;
    }

    public function subtract(int $amount, \DateTimeImmutable $moment): Miles
    {
        $currentAmount = $this->getAmountFor($moment);
        if($currentAmount < $amount) {
            throw new \InvalidArgumentException('Insufficient amount of miles');
        }
        return new self($currentAmount - $amount, $this->whenFirstHalfExpires, $this->whenExpires);
    }

    public function expiresAt(): ?\DateTimeImmutable
    {
        return $this->whenExpires;
    }

    public function getWhenFirstHalfExpires(): \DateTimeImmutable
    {
        return $this->whenFirstHalfExpires;
    }
}
