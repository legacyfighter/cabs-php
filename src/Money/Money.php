<?php

namespace LegacyFighter\Cabs\Money;

class Money
{
    private int $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public static function from(int $value): self
    {
        return new self($value);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(self $other): self
    {
        return new self($this->value - $other->value);
    }

    public function percentage(int $percentage): self
    {
        return new self((int) round($percentage * $this->value/100));
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toString(): string
    {
        return sprintf('%.2f', $this->value / 100);
    }
}
