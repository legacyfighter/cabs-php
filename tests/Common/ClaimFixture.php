<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\DTO\ClaimDTO;
use LegacyFighter\Cabs\Entity\Claim;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Service\ClaimService;

class ClaimFixture
{
    public function __construct(
        private ClaimService $claimService,
    )
    {
    }

    public function createClaim(Client $client, Transit $transit, string $reason = '$$$'): Claim
    {
        $claimDto = ClaimDTO::with('Okradli mnie na hajs', $reason, $client->getId(), $transit->getId());
        $claimDto->setIsDraft(false);
        return $this->claimService->create($claimDto);
    }

    public function createAndResolveClaim(Client $client, Transit $transit): Claim
    {
        $claim = $this->createClaim($client, $transit);
        $this->claimService->tryToResolveAutomatically($claim->getId());
        return $claim;
    }
}
