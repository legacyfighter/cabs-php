<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Ride\Transit;
use LegacyFighter\Cabs\Ride\TransitRepository;
use LegacyFighter\Cabs\Tests\Double\FakeTariffs;

class StubbedTransitPrice
{
    public function __construct(
        private FakeTariffs $fakeTariffs
    )
    {
    }

    public function stub(Money $faked): void
    {
        $this->fakeTariffs->setFakeTariff(Tariff::of(0, 'faked', $faked));
    }
}
