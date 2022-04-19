<?php

namespace LegacyFighter\Cabs\Geolocation;

use LegacyFighter\Cabs\Geolocation\Address\Address;

class GeocodingService
{
    /**
     * @return float[]
     */
    public function geocodeAddress(Address $address): array
    {
        //TODO ... call do zewnętrznego serwisu
        $geocoded = [];

        $geocoded[0] = 1.0; //latitude
        $geocoded[1] = 1.0; //longitude

        return $geocoded;
    }
}
