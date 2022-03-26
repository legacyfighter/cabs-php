<?php

namespace LegacyFighter\Cabs\Repair\Model\Roles\Assistance;

use LegacyFighter\Cabs\Party\Model\Role\PartyBasedRole;
use LegacyFighter\Cabs\Repair\Api\AssistanceRequest;

abstract class RoleForAssistance extends PartyBasedRole
{
    public abstract function handle(AssistanceRequest $request);
}
