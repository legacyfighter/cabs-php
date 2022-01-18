<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\Entity\Claim;
use LegacyFighter\Cabs\Repository\ClaimRepository;

class ClaimNumberGenerator
{
    private ClaimRepository $claimRepository;

    public function __construct(ClaimRepository $claimRepository)
    {
        $this->claimRepository = $claimRepository;
    }

    public function generate(Claim $claim): string
    {
        $count = $this->claimRepository->count();
        $prefix = $count;
        if($count===0) {
            $prefix=1;
        }

        return sprintf('%s---%s', $prefix, $claim->getCreationDate()->format('d/m/Y'));
    }
}
