<?php

namespace LegacyFighter\Cabs\TransitAnalyzer;

use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Repository\TransitRepository;
use LegacyFighter\Cabs\TransitDetails\TransitDetailsFacade;

class PopulateGraphService
{
    public function __construct(
        private TransitRepository $transitRepository,
        private GraphTransitAnalyzer $graphTransitAnalyzer,
        private TransitDetailsFacade $transitDetailsFacade
    )
    {
    }

    public function populate(): void
    {
        foreach ($this->transitRepository->findAllByStatus(Transit::STATUS_COMPLETED) as $transit) {
            $transitDetails = $this->transitDetailsFacade->find($transit->getId());
            $this->graphTransitAnalyzer->addTransitBetweenAddresses(
                $transitDetails->client->getId(),
                $transit->getId(),
                $transitDetails->from->getHash(),
                $transitDetails->to->getHash(),
                $transitDetails->started,
                $transitDetails->completedAt
            );
        }
    }
}
