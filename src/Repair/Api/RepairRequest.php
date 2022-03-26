<?php

namespace LegacyFighter\Cabs\Repair\Api;

use LegacyFighter\Cabs\Party\Api\PartyId;

class RepairRequest
{
    private PartyId $vehicle;

    /**
     * @var string[]
     */
    private array $partsToRepair;

    public function __construct(PartyId $vehicle, array $partsToRepair)
    {
        $this->vehicle = $vehicle;
        $this->partsToRepair = $partsToRepair;
    }

    public function getVehicle(): PartyId
    {
        return $this->vehicle;
    }

    public function getPartsToRepair(): array
    {
        return $this->partsToRepair;
    }
}
