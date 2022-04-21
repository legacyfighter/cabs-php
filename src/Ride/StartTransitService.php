<?php

namespace LegacyFighter\Cabs\Ride;

use Symfony\Component\Uid\Uuid;

class StartTransitService
{
    public function __construct(
        private TransitRepository $transitRepository,
        private RequestTransitService $requestTransitService
    )
    {
    }

    public function start(Uuid $requestUuid): Transit
    {
        return $this->transitRepository->save(new Transit($this->requestTransitService->findTariff($requestUuid), $requestUuid));
    }
}
