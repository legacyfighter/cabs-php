<?php

namespace LegacyFighter\Cabs\TransitDetails;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\DTO\AddressDTO;
use LegacyFighter\Cabs\DTO\ClientDTO;

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
        public string $tariffName
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
            $td->getTariff()->getName()
        );
    }
}
