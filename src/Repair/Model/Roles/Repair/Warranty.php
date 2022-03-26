<?php

namespace LegacyFighter\Cabs\Repair\Model\Roles\Repair;

use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repair\Api\RepairRequest;

class Warranty extends RoleForRepairer
{
    public function handle(RepairRequest $request): RepairingResult
    {
        return new RepairingResult($this->party->getId(), Money::zero(), $request->getPartsToRepair());
    }
}
