<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class AwardsAccount extends BaseEntity
{
    #[OneToOne(targetEntity: Client::class)]
    private Client $client;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

    #[Column(type: 'boolean')]
    private bool $isActive;

    #[Column(type: 'integer')]
    private int $transactions = 0;

    public function __construct()
    {
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getTransactions(): int
    {
        return $this->transactions;
    }

    public function increaseTransactions(): void
    {
        $this->transactions++;
    }
}
