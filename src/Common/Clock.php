<?php

namespace LegacyFighter\Cabs\Common;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
