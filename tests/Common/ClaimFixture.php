<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Crm\Claims\Claim;
use LegacyFighter\Cabs\Crm\Claims\ClaimDTO;
use LegacyFighter\Cabs\Crm\Claims\ClaimService;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\Ride\Transit;
use LegacyFighter\Cabs\Ride\TransitDTO;

class ClaimFixture
{
    public function __construct(
        private ClaimService $claimService,
    )
    {
    }

    public function createClaim(Client $client, TransitDTO $transit, string $reason = '$$$'): Claim
    {
        $claimDto = ClaimDTO::with('Okradli mnie na hajs', $reason, $client->getId(), $transit->getId());
        $claimDto->setIsDraft(false);
        return $this->claimService->create($claimDto);
    }

    public function createAndResolveClaim(Client $client, TransitDTO $transit): Claim
    {
        $claim = $this->createClaim($client, $transit);
        $this->claimService->tryToResolveAutomatically($claim->getId());
        return $claim;
    }
}
