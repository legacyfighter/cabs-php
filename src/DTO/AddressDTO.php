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

    private function __construct(Address $address)
    {
        $this->country = $address->getCountry();
        $this->city = $address->getCity();
        $this->street = $address->getStreet();
        $this->buildingNumber = $address->getBuildingNumber();
        $this->additionalNumber = $address->getAdditionalNumber();
        $this->postalCode = $address->getPostalCode();
        $this->name = $address->getName();
    }

    public static function from(Address $address): self
    {
        return new self($address);
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
