<?php

namespace LegacyFighter\Cabs\Party\Model\Role;

use LegacyFighter\Cabs\Party\Model\Party\Party;

abstract class PartyBasedRole
{
    protected Party $party;

    public function __construct(Party $party)
    {
        $this->party = $party;
    }
}
