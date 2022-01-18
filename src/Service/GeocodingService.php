<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\Entity\Address;

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
