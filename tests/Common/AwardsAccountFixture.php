<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Loyalty\AwardsService;

class AwardsAccountFixture
{
    public function __construct(
        private AwardsService $awardsService
    )
    {
    }

    public function awardsAccount(Client $client): void
    {
        $this->awardsService->registerToProgram($client->getId());
    }

    public function activeAwardsAccount(Client $client): void
    {
        $this->awardsAccount($client);
        $this->awardsService->activateAccount($client->getId());
    }
}
