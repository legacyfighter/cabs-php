<?php

namespace LegacyFighter\Cabs\Loyalty;

class ConstantUntil implements Miles
{
    private int $amount;
    private ?\DateTimeImmutable $whenExpires;

    private function __construct(int $amount, ?\DateTimeImmutable $whenExpires)
    {
        $this->amount = $amount;
        $this->whenExpires = $whenExpires;
    }

    public static function untilForever(int $amount): self
    {
        return new self($amount, null);
    }

    public static function until(int $amount, \DateTimeImmutable $when): self
    {
        return new self($amount, $when);
    }

    public function getAmountFor(\DateTimeImmutable $moment): int
    {
        return $this->whenExpires === null || $this->whenExpires >= $moment ? $this->amount : 0;
    }

    public function subtract(int $amount, \DateTimeImmutable $moment): Miles
    {
        if($this->getAmountFor($moment) < $amount) {
            throw new \InvalidArgumentException('Insufficient amount of miles');
        }
        return new self($this->amount - $amount, $this->whenExpires);
    }

    public function expiresAt(): ?\DateTimeImmutable
    {
        return $this->whenExpires;
    }
}
