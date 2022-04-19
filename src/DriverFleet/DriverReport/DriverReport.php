<?php

namespace LegacyFighter\Cabs\DriverFleet\DriverReport;

use LegacyFighter\Cabs\DriverFleet\DriverAttributeDTO;
use LegacyFighter\Cabs\DriverFleet\DriverDTO;
use LegacyFighter\Cabs\DTO\DriverSessionDTO;
use LegacyFighter\Cabs\DTO\TransitDTO;

class DriverReport implements \JsonSerializable
{
    private DriverDTO $driverDTO;

    /**
     * @var DriverAttributeDTO[]
     */
    private array $attributes = [];

    /**
     * @var array<array{session: DriverSessionDTO, transits: TransitDTO[]}>
     */
    private array $sessions = [];

    public function __construct()
    {
    }

    public function getDriverDTO(): DriverDTO
    {
        return $this->driverDTO;
    }

    public function setDriverDTO(DriverDTO $driverDTO): void
    {
        $this->driverDTO = $driverDTO;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param DriverAttributeDTO[] $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function addAttr(string $name, string $value): void
    {
        $this->attributes[] = DriverAttributeDTO::with($name, $value);
    }

    public function getSessions(): array
    {
        return $this->sessions;
    }

    /**
     * @param array<array{session: DriverSessionDTO, transits: TransitDTO[]}> $sessions
     */
    public function setSessions(array $sessions): void
    {
        $this->sessions = $sessions;
    }

    public function jsonSerialize()
    {
        return [
            'driver' => $this->driverDTO,
            'attributes' => $this->attributes,
            'sessions' => $this->sessions
        ];
    }
}
