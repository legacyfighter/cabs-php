<?php

namespace LegacyFighter\Cabs\Ride;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Pricing\Tariff;
use Symfony\Component\Uid\Uuid;

#[Entity]
class RequestForTransit extends BaseEntity
{
    #[Column(type: 'uuid')]
    private Uuid $requestUuid;

    #[Embedded(class: Tariff::class)]
    private Tariff $tariff;

    #[Column(type: 'distance')]
    private Distance $distance;

    public function __construct(Tariff $tariff, Distance $distance)
    {
        $this->requestUuid = Uuid::v4();
        $this->tariff = $tariff;
        $this->distance = $distance;
    }

    public function getEstimatedPrice(): Money
    {
        return $this->tariff->calculateCost($this->distance);
    }

    public function getRequestUuid(): Uuid
    {
        return $this->requestUuid;
    }

    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    public function getDistance(): Distance
    {
        return $this->distance;
    }
}
