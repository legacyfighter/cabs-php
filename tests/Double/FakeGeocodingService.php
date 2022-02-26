<?php

namespace LegacyFighter\Cabs\Tests\Double;

use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Service\GeocodingService;

class FakeGeocodingService extends GeocodingService
{
    private array $returnValues = [];
    private array $addressMap = [];

    public function geocodeAddress(Address $address): array
    {
        if(isset($this->addressMap[$address->getId()])) {
            return $this->addressMap[$address->getId()];
        }

        if($this->returnValues !== []) {
            return array_shift($this->returnValues);
        }

        return [1.0, 1.0];
    }

    public function setReturnValues(array $returnValues): void
    {
        $this->returnValues = $returnValues;
    }

    public function setValuesForAddress(Address $address, array $values): void
    {
        $this->addressMap[$address->getId()] = $values;
    }
}
