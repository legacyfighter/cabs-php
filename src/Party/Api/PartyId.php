<?php

namespace LegacyFighter\Cabs\Party\Api;

use Symfony\Component\Uid\Uuid;

class PartyId
{
    private Uuid $id;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function toUuid(): Uuid
    {
        return $this->id;
    }
}
