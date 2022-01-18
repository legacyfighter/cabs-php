<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\DriverAttribute;

class DriverAttributeDTO implements \JsonSerializable
{
    private string $name;
    private string $value;

    private function __construct(DriverAttribute $driverAttribute)
    {
        $this->name = $driverAttribute->getName();
        $this->value = $driverAttribute->getValue();
    }

    public static function from(DriverAttribute $driverAttribute): self
    {
        return new self($driverAttribute);
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value
        ];
    }
}
