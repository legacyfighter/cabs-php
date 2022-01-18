<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class DriverFee extends BaseEntity
{
    public const TYPE_FLAT = 'flat';
    public const TYPE_PERCENTAGE = 'percentage';

    #[Column]
    private string $type;

    #[OneToOne(targetEntity: Driver::class)]
    private Driver $driver;

    #[Column(type: 'integer')]
    private int $amount;

    #[Column(type: 'integer', nullable: true)]
    private ?int $min;

    public function __construct(string $type, Driver $driver, int $amount, ?int $min = null)
    {
        $this->type = $type;
        $this->driver = $driver;
        $this->amount = $amount;
        $this->min = $min;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function setDriver(Driver $driver): void
    {
        $this->driver = $driver;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function setMin(?int $min): void
    {
        $this->min = $min;
    }
}
