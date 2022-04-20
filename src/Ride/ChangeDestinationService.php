<?php

namespace LegacyFighter\Cabs\Ride;

use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Geolocation\DistanceCalculator;
use LegacyFighter\Cabs\Geolocation\GeocodingService;
use Symfony\Component\Uid\Uuid;

class ChangeDestinationService
{
    public function __construct(
        private TransitRepository $transitRepository,
        private DistanceCalculator $distanceCalculator,
        private GeocodingService $geocodingService
    )
    {
    }

    public function changeTransitAddressTo(Uuid $requestUuid, Address $newAddress, Address $from): Distance
    {
        // FIXME later: add some exceptions handling
        $geoFrom = $this->geocodingService->geocodeAddress($from);
        $geoTo = $this->geocodingService->geocodeAddress($newAddress);
        $newDistance = Distance::ofKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));
        $transit = $this->transitRepository->findByTransitRequestUuid($requestUuid);
        $transit?->changeDestination($newDistance);
        return $newDistance;
    }
}
