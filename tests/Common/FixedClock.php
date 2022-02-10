<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Common\Clock;

class FixedClock implements Clock
{
    private ?\DateTimeImmutable $fixed = null;

    public function now(): \DateTimeImmutable
    {
        return $this->fixed ?? new \DateTimeImmutable();
    }

    public function setDateTime(?\DateTimeImmutable $dateTime): void
    {
        $this->fixed = $dateTime;
    }
}
