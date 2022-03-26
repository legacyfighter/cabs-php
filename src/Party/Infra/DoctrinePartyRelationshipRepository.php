<?php

namespace LegacyFighter\Cabs\Party\Infra;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Party\Api\PartyId;
use LegacyFighter\Cabs\Party\Model\Party\Party;
use LegacyFighter\Cabs\Party\Model\Party\PartyRelationship;
use LegacyFighter\Cabs\Party\Model\Party\PartyRelationshipRepository;
use Munus\Control\Option;

class DoctrinePartyRelationshipRepository implements PartyRelationshipRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function put(string $partyRelationship, string $partyARole, Party $partyA, string $partyBRole, Party $partyB): PartyRelationship
    {
        $parties = $this->em->createQuery(sprintf('SELECT r FROM %s r WHERE
            r.name = :name AND (
                (r.partyA = :partyA AND r.partyB = :partyB) OR 
                (r.partyA = :partyB AND r.partyB = :partyA)
            )
        ', PartyRelationship::class))->setParameters([
            'name' => $partyRelationship,
            'partyA' => $partyA,
            'partyB' => $partyB
        ])->getResult();

        if($parties === []) {
            $relationship = new PartyRelationship();
            $this->em->persist($relationship);
        } else {
            $relationship = $parties[0];
        }

        $relationship->setName($partyRelationship);
        $relationship->setPartyA($partyA);
        $relationship->setPartyB($partyB);
        $relationship->setRoleA($partyARole);
        $relationship->setRoleB($partyBRole);
        $this->em->flush();

        return $relationship;
    }

    public function findRelationshipFor(PartyId $id, string $relationshipName): Option
    {
        $parties = $this->em->createQuery(sprintf('SELECT r FROM %s r WHERE
            r.name = :name AND (r.partyA = :id OR r.partyB = :id)
        ', PartyRelationship::class))->setParameters([
            'name' => $relationshipName,
            'id' => $id->toUuid(),
        ])->getResult();

        if($parties===[]) {
            return Option::none();
        }

        return Option::some($parties[0]);
    }

}
