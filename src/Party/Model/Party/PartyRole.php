<?php

namespace LegacyFighter\Cabs\Party\Model\Party;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Uid\Uuid;

#[Entity]
class PartyRole
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    private string $name;

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
}
