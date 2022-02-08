<?php

namespace LegacyFighter\Cabs\Tests\Double;

use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Service\GeocodingService;

class FakeGeocodingService extends GeocodingService
{
    private array $returnValues = [];

    public function geocodeAddress(Address $address): array
    {
        if($this->returnValues !== []) {
            return array_shift($this->returnValues);
        }

        return [1.0, 1.0];
    }

    public function setReturnValues(array $returnValues): void
    {
        $this->returnValues = $returnValues;
    }
}
