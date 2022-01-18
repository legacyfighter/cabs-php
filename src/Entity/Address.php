<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Common\ObjectHash;

#[Entity]
class Address extends BaseEntity
{
    #[Column]
    private string $country;

    #[Column]
    private string $district;

    #[Column]
    private string $city;

    #[Column]
    private string $street;

    #[Column(type: 'integer')]
    private int $buildingNumber;

    #[Column(type: 'integer', nullable: true)]
    private ?int $additionalNumber = null;

    #[Column]
    private string $postalCode;

    #[Column]
    private string $name;

    #[Column(type: 'bigint', unique: true)]
    private int $hash;

    public function __construct(string $country, string $city, string $street, int $buildingNumber)
    {
        $this->country = $country;
        $this->city = $city;
        $this->street = $street;
        $this->buildingNumber = $buildingNumber;
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

    public function setAdditionalNumber(?int $additionalNumber): void
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

    public function getHash(): int
    {
        return $this->hash;
    }

    public function hash(): void
    {
        $this->hash = ObjectHash::hash($this->country, $this->district, $this->city, $this->street, $this->buildingNumber, $this->additionalNumber, $this->postalCode, $this->name);
    }
}
