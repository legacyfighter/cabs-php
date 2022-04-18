<?php

namespace LegacyFighter\Cabs\TransitDetails;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Tariff;
use LegacyFighter\Cabs\Money\Money;

class TransitDetailsFacade
{
    public function __construct(
        private TransitDetailsRepository $transitDetailsRepository
    )
    {
    }

    public function find(int $transitId): TransitDetailsDTO
    {
        return TransitDetailsDTO::from($this->load($transitId));
    }

    public function transitRequested(\DateTimeImmutable $when, int $transitId, Address $from, Address $to, Distance $distance, Client $client, string $carType, Money $estimatedPrice, Tariff $tariff): void
    {
        $this->transitDetailsRepository->save(
            new TransitDetails($when, $transitId, $from, $to, $distance, $client, $carType, $estimatedPrice, $tariff)
        );
    }

    public function pickupChangedTo(int $transitId, Address $newAddress, Distance $newDistance): void
    {
        $this->load($transitId)->pickupChangedTo($newAddress, $newDistance);
    }

    public function destinationChanged(int $transitId, Address $newAddress, Distance $newDistance): void
    {
        $this->load($transitId)->destinationChangedTo($newAddress, $newDistance);
    }

    public function transitPublished(int $transitId, \DateTimeImmutable $when): void
    {
        $this->load($transitId)->publishedAt($when);
    }

    public function transitStarted(int $transitId, \DateTimeImmutable $when): void
    {
        $this->load($transitId)->startedAt($when);
    }

    public function transitAccepted(int $transitId, \DateTimeImmutable $when, int $driverId): void
    {
        $this->load($transitId)->acceptedAt($when, $driverId);
    }

    public function transitCancelled(int $transitId): void
    {
        $this->load($transitId)->cancelled();
    }

    public function transitCompleted(int $transitId, \DateTimeImmutable $when, Money $price, Money $driverFee): void
    {
        $this->load($transitId)->completedAt($when, $price, $driverFee);
    }

    /**
     * @return TransitDetailsDTO[]
     */
    public function findByClient(int $clientId): array
    {
        return array_map(fn(TransitDetails $td) => TransitDetailsDTO::from($td), $this->transitDetailsRepository->findByClientId($clientId));
    }

    /**
     * @return TransitDetailsDTO[]
     */
    public function findByDriver(int $driverId, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return array_map(fn(TransitDetails $td) => TransitDetailsDTO::from($td), $this->transitDetailsRepository->findAllByDriverAndDateTimeBetween($driverId, $from, $to));
    }

    private function load(int $transitId): TransitDetails
    {
        return $this->transitDetailsRepository->findByTransitId($transitId);
    }
}
