<?php

namespace LegacyFighter\Cabs\Tests\Double;

use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Pricing\Tariffs;

class FakeTariffs extends Tariffs
{
    private ?Tariff $fakeTariff = null;

    public function setFakeTariff(Tariff $tariff): void
    {
        $this->fakeTariff = $tariff;
    }

    public function choose(?\DateTimeImmutable $when = null): Tariff
    {
        if($this->fakeTariff !== null) {
            return $this->fakeTariff;
        }

        return parent::choose($when);
    }

}
