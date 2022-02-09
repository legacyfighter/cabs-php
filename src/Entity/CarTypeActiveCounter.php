<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class CarTypeActiveCounter
{
    #[Id]
    #[Column]
    private string $carClass;

    #[Column(type: 'integer')]
    private int $activeCarsCounter = 0;

    public function __construct(string $carClass)
    {
        $this->carClass = $carClass;
    }

    public function getActiveCarsCounter(): int
    {
        return $this->activeCarsCounter;
    }
}
