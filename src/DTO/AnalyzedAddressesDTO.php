<?php

namespace LegacyFighter\Cabs\DTO;

class AnalyzedAddressesDTO implements \JsonSerializable
{
    /**
     * @var AddressDTO[]
     */
    private array $addresses;

    /**
     * @var AddressDTO[]
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
