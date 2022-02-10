<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class AwardedMiles extends BaseEntity
{
    #[ManyToOne(targetEntity: Client::class)]
    private Client $client;

    #[Column(type: 'integer')]
    private int $miles;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expirationDate = null;

    #[Column(type: 'boolean')]
    private bool $isSpecial;

    #[ManyToOne(targetEntity: Transit::class)]
    private ?Transit $transit = null;

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

    public function getMiles(): int
    {
        return $this->miles;
    }

    public function setMiles(int $miles): void
    {
        $this->miles = $miles;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getExpirationDate(): ?\DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTimeImmutable $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function isSpecial(): bool
    {
        return $this->isSpecial;
    }

    public function setSpecial(bool $isSpecial): void
    {
        $this->isSpecial = $isSpecial;
    }

    public function getTransit(): ?Transit
    {
        return $this->transit;
    }

    public function setTransit(?Transit $transit): void
    {
        $this->transit = $transit;
    }
}
