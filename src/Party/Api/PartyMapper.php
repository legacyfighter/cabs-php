<?php

namespace LegacyFighter\Cabs\Party\Api;

use LegacyFighter\Cabs\Party\Model\Party\PartyRelationship;
use LegacyFighter\Cabs\Party\Model\Party\PartyRelationshipRepository;
use Munus\Control\Option;

class PartyMapper
{
    private PartyRelationshipRepository $repository;

    public function __construct(PartyRelationshipRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Option<PartyRelationship>
     */
    public function mapRelation(PartyId $id, string $relationshipName): Option
    {
        return $this->repository->findRelationshipFor($id, $relationshipName);
    }
}
