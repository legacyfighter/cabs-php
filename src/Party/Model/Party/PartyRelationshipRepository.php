<?php

namespace LegacyFighter\Cabs\Party\Model\Party;

use LegacyFighter\Cabs\Party\Api\PartyId;
use Munus\Control\Option;

interface PartyRelationshipRepository
{
    public function put(string $partyRelationship, string $partyARole, Party $partyA, string $partyBRole, Party $partyB): PartyRelationship;

    /**
     * @return Option<PartyRelationship>
     */
    public function findRelationshipFor(PartyId $id, string $relationshipName): Option;
}
