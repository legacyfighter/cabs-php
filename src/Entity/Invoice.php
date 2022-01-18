<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class Invoice extends BaseEntity
{
    #[Column(type: 'float')]
    private float $amount;

    #[Column]
    private string $subjectName;

    public function __construct(float $amount, string $subjectName)
    {
        $this->amount = $amount;
        $this->subjectName = $subjectName;
    }
}
