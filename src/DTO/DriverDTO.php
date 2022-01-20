<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\Driver;

class DriverDTO implements \JsonSerializable
{
    private int $id;
    private string $firstName;
    private string $lastName;
    private string $driverLicense;
    private ?string $photo;
    private string $status;
    private string $type;

    public function __construct(Driver $driver)
    {
        $this->id = $driver->getId();
        $this->firstName = $driver->getFirstName();
        $this->lastName = $driver->getLastName();
        $this->driverLicense = $driver->getDriverLicense()->asString();
        $this->photo = $driver->getPhoto();
        $this->status = $driver->getStatus();
        $this->type = $driver->getType();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getDriverLicense(): string
    {
        return $this->driverLicense;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'driverLicense' => $this->driverLicense,
            'photo' => $this->photo,
            'status' => $this->status,
            'type' => $this->type
        ];
    }


}
