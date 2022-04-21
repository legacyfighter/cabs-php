<?php

namespace LegacyFighter\Cabs\Ride;

use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Geolocation\DistanceCalculator;
use LegacyFighter\Cabs\Geolocation\GeocodingService;
use LegacyFighter\Cabs\Money\Money;
use Symfony\Component\Uid\Uuid;

class CompleteTransitService
{
    public function __construct(
        private TransitRepository $transitRepository,
        private DistanceCalculator $distanceCalculator,
        private GeocodingService $geocodingService
    )
    {
    }

    public function completeTransit(int $driverId, Uuid $requestUuid, Address $from, Address $destinationAddress): Money
    {
        $transit = $this->transitRepository->findByTransitRequestUuid($requestUuid);
        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }

        // FIXME later: add some exceptions handling
        $geoFrom = $this->geocodingService->geocodeAddress($from);
        $geoTo = $this->geocodingService->geocodeAddress($destinationAddress);
        $distance = Distance::ofKm($this->distanceCalculator->calculateByMap($geoFrom[0], $geoFrom[1], $geoTo[0], $geoTo[1]));
        $finalPrice = $transit->completeAt($distance);
        $this->transitRepository->save($transit);

        return $finalPrice;
    }
}
