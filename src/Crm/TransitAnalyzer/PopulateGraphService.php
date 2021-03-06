<?php

namespace LegacyFighter\Cabs\Crm\TransitAnalyzer;

use LegacyFighter\Cabs\Ride\Details\TransitDetailsFacade;

class PopulateGraphService
{
    public function __construct(
        private GraphTransitAnalyzer $graphTransitAnalyzer,
        private TransitDetailsFacade $transitDetailsFacade
    )
    {
    }

    public function populate(): void
    {
        foreach ($this->transitDetailsFacade->findCompleted() as $transit) {
            $transitDetails = $this->transitDetailsFacade->find($transit->transitId);
            $this->graphTransitAnalyzer->addTransitBetweenAddresses(
                $transitDetails->client->getId(),
                $transit->transitId,
                $transitDetails->from->getHash(),
                $transitDetails->to->getHash(),
                $transitDetails->started,
                $transitDetails->completedAt
            );
        }
    }
}
