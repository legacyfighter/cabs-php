<?php

namespace LegacyFighter\Cabs\Repair\Model\Roles\Repair;

use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repair\Api\RepairRequest;
use LegacyFighter\Cabs\Repair\Legacy\Parts\Parts;

class ExtendedInsurance extends RoleForRepairer
{
    public function handle(RepairRequest $request): RepairingResult
    {
        $handledParts = array_filter($request->getPartsToRepair(), fn(string $part) => $part !== Parts::PAINT);

        return new RepairingResult($this->party->getId(), Money::zero(), $handledParts);
    }

}
