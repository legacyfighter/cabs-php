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
class TransitDemand extends BaseEntity
{
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_WAITING_FOR_DRIVER_ASSIGNMENT = 'waiting-for-driver-assigment';
    public const STATUS_TRANSIT_TO_PASSENGER = 'transit-to-passenger';

    #[Column(type: 'uuid')]
    private Uuid $requestUuid;

    #[Column]
    private string $status;

    #[Column(type: 'integer')]
    private int $pickupAddressChangeCounter = 0;

    public function __construct(Uuid $requestUuid)
    {
        $this->requestUuid = $requestUuid;
        $this->status = self::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT;
    }

    public function changePickupTo(float $distanceFromPreviousPickup): void
    {
        if($distanceFromPreviousPickup > 0.25) {
            throw new \InvalidArgumentException('Address \'from\' cannot be changed, id ='.$this->requestUuid);
        }

        if(!in_array($this->status, [self::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT], true)) {
            throw new \InvalidArgumentException('Address \'from\' cannot be changed, id ='.$this->requestUuid);
        }

        if($this->pickupAddressChangeCounter > 2) {
            throw new \InvalidArgumentException('Address \'from\' cannot be changed, id ='.$this->requestUuid);
        }

        $this->pickupAddressChangeCounter++;
    }

    public function accept(): void
    {
        $this->status = self::STATUS_TRANSIT_TO_PASSENGER;
    }

    public function cancel(): void
    {
        if($this->status !== self::STATUS_WAITING_FOR_DRIVER_ASSIGNMENT ) {
            throw new \InvalidArgumentException('Demand cannot be cancelled, id = '.$this->requestUuid);
        }
        $this->status = self::STATUS_CANCELLED;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
