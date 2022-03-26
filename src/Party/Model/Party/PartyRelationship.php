<?php

namespace LegacyFighter\Cabs\Party\Model\Party;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Uid\Uuid;

#[Entity]
class PartyRelationship
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column]
    private string $name;

    #[Column]
    private string $roleA;

    #[Column]
    private string $roleB;

    #[ManyToOne(targetEntity: Party::class)]
    private Party $partyA;

    #[ManyToOne(targetEntity: Party::class)]
    private Party $partyB;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getRoleA(): string
    {
        return $this->roleA;
    }

    public function setRoleA(string $roleA): void
    {
        $this->roleA = $roleA;
    }

    public function getRoleB(): string
    {
        return $this->roleB;
    }

    public function setRoleB(string $roleB): void
    {
        $this->roleB = $roleB;
    }

    public function getPartyA(): Party
    {
        return $this->partyA;
    }

    public function setPartyA(Party $partyA): void
    {
        $this->partyA = $partyA;
    }

    public function getPartyB(): Party
    {
        return $this->partyB;
    }

    public function setPartyB(Party $partyB): void
    {
        $this->partyB = $partyB;
    }
}
