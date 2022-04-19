<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Address\AddressRepository;

class AddressFixture
{
    public function __construct(
        private AddressRepository $addressRepository
    )
    {
    }

    public function anAddress(string $country, string $city, string $street, int $buildingNumber): Address
    {
        $address = new Address($country, $city, $street, $buildingNumber);
        $address->setPostalCode('11-111');
        $address->setName('Home');
        $address->setDistrict('district');
        return $this->addressRepository->save($address);
    }
}
