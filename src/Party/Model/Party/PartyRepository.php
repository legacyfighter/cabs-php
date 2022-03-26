<?php

namespace LegacyFighter\Cabs\Party\Model\Party;

use Symfony\Component\Uid\Uuid;

interface PartyRepository
{
    public function put(Uuid $id): Party;
}
