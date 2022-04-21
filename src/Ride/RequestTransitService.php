<?php

namespace LegacyFighter\Cabs\Ride;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Geolocation\DistanceCalculator;
use LegacyFighter\Cabs\Geolocation\GeocodingService;
use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Pricing\Tariffs;
use Symfony\Component\Uid\Uuid;

class RequestTransitService
{
    public function __construct(
        private DistanceCalculator $distanceCalculator,
        private GeocodingService $geocodingService,
        private Clock $clock,
        private RequestForTransitRepository $requestForTransitRepository,
        private Tariffs $tariffs
    )
    {
    }

    public function createRequestForTransit(Address $from, Address $to): RequestForTransit
    {
        // FIXME later: add some exceptions handling
        $geoFrom = $this->geocodingService->geocodeAddress($from);
        $geoTo = $this->geocodingService->geocodeAddress($to);
        $distance = Distance::ofKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));
        $now = $this->clock->now();
        $tariff = $this->tariffs->choose($now);
        return $this->requestForTransitRepository->save(new RequestForTransit($tariff, $distance));
    }

    public function findCalculationUUID(int $requestId): Uuid
    {
        return $this->requestForTransitRepository->getOne($requestId)->getRequestUuid();
    }

    public function findTariff(Uuid $requestUuid): Tariff
    {
        return $this->requestForTransitRepository->findByRequestUuid($requestUuid)->getTariff();
    }
}
