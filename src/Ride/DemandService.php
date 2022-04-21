<?php

namespace LegacyFighter\Cabs\Ride;

use Symfony\Component\Uid\Uuid;

class DemandService
{
    public function __construct(
        private TransitDemandRepository $transitDemandRepository
    )
    {
    }

    public function publishDemand(Uuid $requestUuid): void
    {
        $this->transitDemandRepository->save(new TransitDemand($requestUuid));
    }

    public function cancelDemand(Uuid $requestUuid): void
    {
        $transitDemand = $this->transitDemandRepository->findByRequestUuid($requestUuid);
        if($transitDemand !== null) {
            $transitDemand->cancel();
        }
    }

    public function acceptDemand(Uuid $requestUuid): void
    {
        $transitDemand = $this->transitDemandRepository->findByRequestUuid($requestUuid);
        if($transitDemand !== null) {
            $transitDemand->accept();
        }
    }

    public function existsFor(Uuid $requestUuid): bool
    {
        return $this->transitDemandRepository->findByRequestUuid($requestUuid) !== null;
    }
}
