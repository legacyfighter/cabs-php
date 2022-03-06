<?php

namespace LegacyFighter\Cabs\TransitAnalyzer;

use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Repository\TransitRepository;

class PopulateGraphService
{
    public function __construct(
        private TransitRepository $transitRepository,
        private GraphTransitAnalyzer $graphTransitAnalyzer
    )
    {
    }

    public function populate(): void
    {
        foreach ($this->transitRepository->findAllByStatus(Transit::STATUS_COMPLETED) as $transit) {
            $this->graphTransitAnalyzer->addTransitBetweenAddresses(
                $transit->getClient()->getId(),
                $transit->getId(),
                $transit->getFrom()->getHash(),
                $transit->getTo()->getHash(),
                $transit->getStarted(),
                $transit->getCompleteAt()
            );
        }
    }
}
