<?php

namespace LegacyFighter\Cabs\Crm\TransitAnalyzer;

use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;

class AnalyzedAddressesDTO implements \JsonSerializable
{
    /**
     * @var AddressDTO[]
     */
    private array $addresses;

    /**
     * @param AddressDTO[] $addresses
     */
    public function __construct(array $addresses)
    {
        $this->addresses = $addresses;
    }

    public function jsonSerialize(): array
    {
        return [
            'addresses' => $this->addresses
        ];
    }
}
