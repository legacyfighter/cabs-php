<?php

namespace LegacyFighter\Cabs\Loyalty;

interface Miles
{
    public function getAmountFor(\DateTimeImmutable $moment): int;

    public function subtract(int $amount, \DateTimeImmutable $moment): Miles;

    public function expiresAt(): ?\DateTimeImmutable;
}
