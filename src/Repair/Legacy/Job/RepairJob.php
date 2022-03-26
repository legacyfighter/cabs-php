<?php

namespace LegacyFighter\Cabs\Repair\Legacy\Job;

use LegacyFighter\Cabs\Money\Money;

class RepairJob extends CommonBaseAbstractJob
{
    /**
     * @var string[];
     */
    private array $partsToRepair;
    private Money $estimatedValue;

    public function __construct()
    {
        $this->partsToRepair = [];
        $this->estimatedValue = Money::zero();
    }

    public function getPartsToRepair(): array
    {
        return $this->partsToRepair;
    }

    public function getEstimatedValue(): Money
    {
        return $this->estimatedValue;
    }

    /**
     * @param string[] $partsToRepair
     */
    public function setPartsToRepair(array $partsToRepair): void
    {
        $this->partsToRepair = $partsToRepair;
    }

    public function setEstimatedValue(Money $estimatedValue): void
    {
        $this->estimatedValue = $estimatedValue;
    }
}
