<?php

namespace LegacyFighter\Cabs\TransitDetails;

use LegacyFighter\Cabs\Crm\ClientDTO;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Money\Money;

class TransitDetailsDTO
{
    public function __construct(
        public int $transitId,
        public \DateTimeImmutable $dateTime,
        public ?\DateTimeImmutable $completedAt,
        public ClientDTO $client,
        public ?string $carType,
        public AddressDTO $from,
        public AddressDTO $to,
        public ?\DateTimeImmutable $started,
        public ?\DateTimeImmutable $acceptedAt,
        public ?\DateTimeImmutable $publishedAt,
        public Distance $distance,
        public float $kmRate,
        public int $baseFee,
        public string $tariffName,
        public ?Money $price,
        public ?Money $driverFee,
        public ?int $driverId
    )
    {
    }

    public static function from(TransitDetails $td): self
    {
        return new self(
            $td->getId(),
            $td->getDateTime(),
            $td->getCompletedAt(),
            ClientDTO::from($td->getClient()),
            $td->getCarType(),
            AddressDTO::from($td->getFrom()),
            AddressDTO::from($td->getTo()),
            $td->getStarted(),
            $td->getAcceptedAt(),
            $td->getPublishedAt(),
            $td->getDistance(),
            $td->getTariff()->getKmRate(),
            $td->getTariff()->getBaseFee(),
            $td->getTariff()->getName(),
            $td->getPrice(),
            $td->getDriversFee(),
            $td->getDriverId()
        );
    }
}
