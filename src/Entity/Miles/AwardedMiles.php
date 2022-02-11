<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Entity\Miles;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Transit;

#[Entity]
class AwardedMiles extends BaseEntity
{
    #[ManyToOne(targetEntity: Client::class)]
    private Client $client;

    #[Column(type: 'miles')]
    private Miles $miles;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

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

    public function getMilesAmount(\DateTimeImmutable $when): int
    {
        return $this->miles->getAmountFor($when);
    }

    public function getMiles(): Miles
    {
        return $this->miles;
    }

    public function setMiles(Miles $miles): void
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
        return $this->miles->expiresAt();
    }

    public function cantExpire(): bool
    {
        return $this->miles->expiresAt() === null;
    }

    public function getTransit(): ?Transit
    {
        return $this->transit;
    }

    public function setTransit(?Transit $transit): void
    {
        $this->transit = $transit;
    }

    public function removeAll(\DateTimeImmutable $when): void
    {
        $this->miles = $this->miles->subtract($this->getMilesAmount($when), $when);
    }

    public function subtract(int $amount, \DateTimeImmutable $when): void
    {
        $this->miles = $this->miles->subtract($amount, $when);
    }
}
