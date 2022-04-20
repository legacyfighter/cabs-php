<?php

namespace LegacyFighter\Cabs\Pricing;

class Tariffs
{
    public function choose(?\DateTimeImmutable $when = null): Tariff
    {
        return Tariff::ofTime($when ?? new \DateTimeImmutable());
    }
}
