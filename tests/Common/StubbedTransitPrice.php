<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repository\TransitRepository;

class StubbedTransitPrice
{
    public function __construct(
        private TransitRepository $transitRepository
    )
    {
    }

    public function stub(int $transitId, Money $faked): Transit
    {
        $transit = $this->transitRepository->getOne($transitId);
        $transit->setPrice($faked);
        return $this->transitRepository->save($transit);
    }
}
