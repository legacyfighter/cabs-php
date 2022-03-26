<?php

namespace LegacyFighter\Cabs\Repair\Model\Roles\Repair;

use LegacyFighter\Cabs\Party\Model\Role\PartyBasedRole;
use LegacyFighter\Cabs\Repair\Api\RepairRequest;

abstract class RoleForRepairer extends PartyBasedRole
{
    public abstract function handle(RepairRequest $request): RepairingResult;
}
