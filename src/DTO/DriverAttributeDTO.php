<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\DriverAttribute;

class DriverAttributeDTO implements \JsonSerializable
{
    private string $name;
    private string $value;

    private function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public static function with(string $name, string $value): self
    {
        return new self($name, $value);
    }

    public static function from(DriverAttribute $driverAttribute): self
    {
        return new self($driverAttribute->getName(), $driverAttribute->getValue());
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value
        ];
    }
}
