<?php

namespace LegacyFighter\Cabs\Entity\Miles;

interface Miles
{
    public function getAmountFor(\DateTimeImmutable $moment): int;

    public function subtract(int $amount, \DateTimeImmutable $moment): Miles;

    public function expiresAt(): ?\DateTimeImmutable;
}
