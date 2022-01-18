<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\Client;

class ClientDTO implements \JsonSerializable
{
    private int $id;
    private string $type;
    private string $name;
    private string $lastName;
    private string $defaultPaymentType;
    private string $clientType;

    private function __construct(Client $client)
    {
        $this->id = $client->getId();
        $this->type = $client->getType();
        $this->name = $client->getName();
        $this->lastName = $client->getLastName();
        $this->defaultPaymentType = $client->getDefaultPaymentType();
        $this->clientType = $client->getClientType();
    }

    public static function from(Client $client): self
    {
        return new self($client);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getDefaultPaymentType(): string
    {
        return $this->defaultPaymentType;
    }

    public function getClientType(): string
    {
        return $this->clientType;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'lastName' => $this->lastName,
            'paymentType' => $this->defaultPaymentType,
            'clientType' => $this->clientType
        ];
    }
}
