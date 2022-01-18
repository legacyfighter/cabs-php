<?php

namespace LegacyFighter\Cabs\DTO;

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

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getSessions(): array
    {
        return $this->sessions;
    }

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
