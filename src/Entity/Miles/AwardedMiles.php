<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Entity\Miles;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Entity\AwardsAccount;
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

    #[ManyToOne(targetEntity: AwardsAccount::class)]
    private AwardsAccount $account;

    public function __construct(AwardsAccount $account, ?Transit $transit, Client $client, \DateTimeImmutable $when, Miles $constantUntil)
    {
        $this->account = $account;
        $this->transit = $transit;
        $this->client = $client;
        $this->date = $when;
        $this->miles = $constantUntil;
    }

    public function transferTo(AwardsAccount $account): void
    {
        $this->client = $account->getClient();
        $this->account = $account;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getMilesAmount(\DateTimeImmutable $when): int
    {
        return $this->miles->getAmountFor($when);
    }

    public function getMiles(): Miles
    {
        return $this->miles;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
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

    public function removeAll(\DateTimeImmutable $when): void
    {
        $this->miles = $this->miles->subtract($this->getMilesAmount($when), $when);
    }

    public function subtract(int $amount, \DateTimeImmutable $when): void
    {
        $this->miles = $this->miles->subtract($amount, $when);
    }
}
