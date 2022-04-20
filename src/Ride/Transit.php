<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Ride;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Pricing\Tariff;
use Symfony\Component\Uid\Uuid;

#[Entity]
class Transit extends BaseEntity
{
    public const STATUS_IN_TRANSIT = 'in-transit';
    public const STATUS_COMPLETED = 'completed';

    #[Column(type: 'uuid')]
    private Uuid $requestUuid;

    #[Column]
    private string $status;

    #[Embedded(class: Tariff::class)]
    private Tariff $tariff;

    #[Column(type: 'float', nullable: true)]
    private ?float $km = null;

    public function __construct(Tariff $tariff, Uuid $requestUuid)
    {
        $this->requestUuid = $requestUuid;
        $this->status = self::STATUS_IN_TRANSIT;
        $this->tariff = $tariff;
    }

    public function changeDestination(Distance $newDistance): void
    {
        if($this->status === self::STATUS_COMPLETED) {
            throw new \RuntimeException('Address "to" cannot be changed, id = '.$this->getRequestUuid());
        }
        $this->km = $newDistance->toKmInFloat();
    }

    public function completeAt(Distance $distance): Money
    {
        if($this->status === self::STATUS_IN_TRANSIT) {
            $this->km = $distance->toKmInFloat();
            $this->status = self::STATUS_COMPLETED;
            return $this->calculateFinalCosts();
        } else {
            throw new \RuntimeException('Cannot complete Transit, id = '.$this->getRequestUuid());
        }
    }

    public function calculateFinalCosts(): Money
    {
        if($this->status === self::STATUS_COMPLETED) {
            return $this->calculateCost();
        }

        throw new \RuntimeException('Cannot calculate final cost if the transit is not completed');
    }

    public function getRequestUuid(): Uuid
    {
        return $this->requestUuid;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    public function getDistance(): ?Distance
    {
        return $this->km === null ? null : Distance::ofKm($this->km);
    }

    private function calculateCost(): Money
    {
        return $this->tariff->calculateCost(Distance::ofKm($this->km));
    }

}
