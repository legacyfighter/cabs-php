<?php

namespace LegacyFighter\Cabs\Service;

class DistanceCalculator
{
    public function calculateByMap(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo): float
    {
        // ...

        return 42.0;
    }

    public function calculateByGeo(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo): float
    {
        // https://www.geeksforgeeks.org/program-distance-two-points-earth/
        // The php contains a function
        // named deg2rad which converts from
        // degrees to radians.
        $lon1 = deg2rad($longitudeFrom);
        $lon2 = deg2rad($longitudeTo);
        $lat1 = deg2rad($latitudeFrom);
        $lat2 = deg2rad($latitudeTo);

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

        return $distanceInKMeters;
    }
}
