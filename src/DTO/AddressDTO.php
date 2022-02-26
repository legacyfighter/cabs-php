<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\Address;

class AddressDTO implements \JsonSerializable
{
    private string $country;
    private string $district;
    private string $city;
    private string $street;
    private int $buildingNumber;
    private ?int $additionalNumber;
    private string $postalCode;
    private string $name;

    public function __construct(string $country, string $district, string $city, string $street, int $buildingNumber, ?int $additionalNumber, string $postalCode, string $name)
    {
        $this->country = $country;
        $this->district = $district;
        $this->city = $city;
        $this->street = $street;
        $this->buildingNumber = $buildingNumber;
        $this->additionalNumber = $additionalNumber;
        $this->postalCode = $postalCode;
        $this->name = $name;
    }

    public static function with(string $country, string $city, string $street, int $buildingNumber, ?int $additionalNumber = null, string $postalCode = '', string $name = '', string $district = ''): self
    {
        return new self($country, $district, $city, $street, $buildingNumber, $additionalNumber, $postalCode, $name);
    }

    public static function from(Address $address): self
    {
        return new self(
            $address->getCountry(),
            $address->getDistrict(),
            $address->getCity(),
            $address->getStreet(),
            $address->getBuildingNumber(),
            $address->getAdditionalNumber(),
            $address->getPostalCode(),
            $address->getName()
        );
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getDistrict(): string
    {
        return $this->district;
    }

    public function setDistrict(string $district): void
    {
        $this->district = $district;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getBuildingNumber(): int
    {
        return $this->buildingNumber;
    }

    public function setBuildingNumber(int $buildingNumber): void
    {
        $this->buildingNumber = $buildingNumber;
    }

    public function getAdditionalNumber(): ?int
    {
        return $this->additionalNumber;
    }

    public function setAdditionalNumber(int $additionalNumber): void
    {
        $this->additionalNumber = $additionalNumber;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function toAddressEntity(): Address
    {
        $address = new Address($this->country, $this->city, $this->street, $this->buildingNumber);
        $address->setAdditionalNumber($this->getAdditionalNumber());
        $address->setName($this->getName());
        $address->setPostalCode($this->getPostalCode());
        $address->setDistrict($this->getDistrict());
        return $address;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'city' => $this->city,
            'street' => $this->street,
            'buildingNumber' => $this->buildingNumber,
            'country' => $this->country
        ];
    }


}
