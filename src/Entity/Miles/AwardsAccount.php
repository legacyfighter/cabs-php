<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Entity\Miles;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
class AwardsAccount extends BaseEntity
{
    #[Column(type: 'integer')]
    private int $clientId;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

    #[Column(type: 'boolean')]
    private bool $isActive;

    #[Column(type: 'integer')]
    private int $transactions = 0;

    #[OneToMany(mappedBy: 'account', targetEntity: AwardedMiles::class, cascade: ['all'])]
    private Collection $miles;

    public function __construct(int $clientId, bool $isActive, \DateTimeImmutable $when)
    {
        $this->clientId = $clientId;
        $this->isActive = $isActive;
        $this->date = $when;
        $this->miles = new ArrayCollection();
    }

    public static function notActiveAccount(int $clientId, \DateTimeImmutable $when): self
    {
        return new self($clientId, false, $when);
    }

    public function addExpiringMiles(int $amount, \DateTimeImmutable $expireAt, int $transitId, \DateTimeImmutable $when): AwardedMiles
    {
        $expiringMiles = new AwardedMiles($this, $transitId, $this->clientId, $when, ConstantUntil::until($amount, $expireAt));
        $this->miles->add($expiringMiles);
        $this->transactions++;
        return $expiringMiles;
    }

    public function addNonExpiringMiles(int $amount, \DateTimeImmutable $when): AwardedMiles
    {
        $nonExpiringMiles = new AwardedMiles($this, null, $this->clientId, $when, ConstantUntil::untilForever($amount));
        $this->miles->add($nonExpiringMiles);
        $this->transactions++;
        return $nonExpiringMiles;
    }

    public function calculateBalance(\DateTimeImmutable $at): int
    {
        return array_sum($this->miles
            ->filter(fn(AwardedMiles $miles) => $miles->getExpirationDate() !== null && $miles->getExpirationDate() > $at || $miles->cantExpire())
            ->map(fn(AwardedMiles $miles) => $miles->getMilesAmount($at))
            ->toArray()
        );
    }

    public function remove(int $miles, \DateTimeImmutable $when, callable $strategy): void
    {
        if($this->calculateBalance($when) >= $miles && $this->isActive) {
            $milesList = $this->miles->toArray();
            $strategy($milesList);
            foreach ($milesList as $iter) {
                if($miles <= 0) {
                    break;
                }
                if($iter->cantExpire() || $iter->getExpirationDate() > $when) {
                    $milesAmount = $iter->getMilesAmount($when);
                    if($milesAmount <= $miles) {
                        $miles -= $milesAmount;
                        $iter->removeAll($when);
                    } else {
                        $iter->subtract($miles, $when);
                        $miles = 0;
                    }
                }
            }
        } else {
            throw new \InvalidArgumentException('Insufficient miles, id = '.$this->clientId.', miles requested = '.$miles);
        }
    }

    public function moveMilesTo(AwardsAccount $accountTo, int $amount, \DateTimeImmutable $when): void
    {
        if($this->calculateBalance($when) >= $amount && $this->isActive()) {
            foreach ($this->miles->toArray() as $iter) {
                /** @var AwardedMiles $iter */
                if($iter->cantExpire() || $iter->getExpirationDate() > $when) {
                    $milesAmount = $iter->getMilesAmount($when);
                    if($milesAmount <= $amount) {
                        $iter->transferTo($accountTo);
                        $amount -= $milesAmount;
                    } else {
                        $iter->subtract($amount, $when);
                        $iter->transferTo($accountTo);
                        $amount -= $iter->getMilesAmount($when);
                    }
                }
            }
            $this->transactions++;
            $accountTo->transactions++;
        }
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getTransactions(): int
    {
        return $this->transactions;
    }

    /**
     * @return AwardedMiles[]
     */
    public function getMiles(): array
    {
        return $this->miles->toArray();
    }
}
