<?php

namespace LegacyFighter\Cabs\Ride;

use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Address\AddressRepository;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Geolocation\DistanceCalculator;
use LegacyFighter\Cabs\Geolocation\GeocodingService;
use Symfony\Component\Uid\Uuid;

class ChangePickupService
{
    public function __construct(
        private DistanceCalculator $distanceCalculator,
        private GeocodingService $geocodingService,
        private AddressRepository $addressRepository,
        private TransitDemandRepository $transitDemandRepository
    )
    {
    }

    public function changeTransitAddressFrom(Uuid $requestUuid, Address $newAddress, Address $oldAddress): Distance
    {
        $newAddress = $this->addressRepository->save($newAddress);
        $transitDemand = $this->transitDemandRepository->findByRequestUuid($requestUuid);
        if($transitDemand === null) {
            throw new \InvalidArgumentException('Transit does not exist, id = '.$requestUuid);
        }

        // FIXME later: add some exceptions handling
        $geoFromNew = $this->geocodingService->geocodeAddress($newAddress);
        $geoFromOld = $this->geocodingService->geocodeAddress($oldAddress);

        // https://www.geeksforgeeks.org/program-distance-two-points-earth/
        // The math module contains a function
        // named toRadians which converts from
        // degrees to radians.
        $lon1 = deg2rad($geoFromNew[1]);
        $lon2 = deg2rad($geoFromOld[1]);
        $lat1 = deg2rad($geoFromNew[0]);
        $lat2 = deg2rad($geoFromOld[0]);

        // Haversine formula
        $dlon = $lon2 - $lon1;
        $dlat = $lat2 - $lat1;
        $a = pow(sin($dlat / 2), 2)
            + cos($lat1) * cos($lat2)
            *pow(sin($dlon/2),2);

        $c = 2 * asin(sqrt($a));

        // Radius of earth in kilometers. Use 3956 for miles
        $r = 6371;

        // calculate the result
        $distanceInKMeters = $c * $r;

        $newDistance = Distance::ofKm($this->distanceCalculator->calculateByMap($geoFromNew[0], $geoFromNew[1], $geoFromOld[0], $geoFromOld[1]));
        $transitDemand->changePickupTo($distanceInKMeters);
        return $newDistance;
    }
}
