<?php

namespace LegacyFighter\Cabs\Repair\Legacy\User;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class SignedContract extends BaseEntity
{
    /**
     * @var string[]
     */
    #[Column(type: 'json')]
    private array $coveredParts;

    #[Column(type: 'float')]
    private float $coverageRatio;

    /**
     * @return string[]
     */
    public function getCoveredParts(): array
    {
        return $this->coveredParts;
    }

    /**
     * @param string[] $coveredParts
     */
    public function setCoveredParts(array $coveredParts): void
    {
        $this->coveredParts = $coveredParts;
    }

    public function getCoverageRatio(): float
    {
        return $this->coverageRatio;
    }

    public function setCoverageRatio(float $coverageRatio): void
    {
        $this->coverageRatio = $coverageRatio;
    }
}
