<?php

namespace LegacyFighter\Cabs\Ride\Details;

use LegacyFighter\Cabs\Assignment\InvolvedDriversSummary;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Ride\Transit;
use Symfony\Component\Uid\Uuid;

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

    public function findByUuid(Uuid $requestUuid): TransitDetailsDTO
    {
        return TransitDetailsDTO::from($this->transitDetailsRepository->findByRequestUuid($requestUuid));
    }

    /**
     * @return TransitDetailsDTO[]
     */
    public function findCompleted(): array
    {
        return array_map(fn(TransitDetails $td) => TransitDetailsDTO::from($td), $this->transitDetailsRepository->findByStatus(Transit::STATUS_COMPLETED));
    }

    public function transitRequested(\DateTimeImmutable $when, Uuid $requestUuid, Address $from, Address $to, Distance $distance, Client $client, string $carType, Money $estimatedPrice, Tariff $tariff): void
    {
        $this->transitDetailsRepository->save(
            new TransitDetails($when, $requestUuid, $from, $to, $distance, $client, $carType, $estimatedPrice, $tariff)
        );
    }

    public function pickupChangedTo(Uuid $requestUuid, Address $newAddress, Distance $newDistance): void
    {
        $this->loadByUuid($requestUuid)->pickupChangedTo($newAddress, $newDistance);
    }

    public function destinationChanged(Uuid $requestUuid, Address $newAddress, Distance $newDistance): void
    {
        $this->loadByUuid($requestUuid)->destinationChangedTo($newAddress, $newDistance);
    }

    public function transitPublished(Uuid $requestUuid, \DateTimeImmutable $when): void
    {
        $this->loadByUuid($requestUuid)->publishedAt($when);
    }

    public function transitStarted(Uuid $requestUuid, int $transitId, \DateTimeImmutable $when): void
    {
        $this->loadByUuid($requestUuid)->startedAt($when, $transitId);
    }

    public function transitAccepted(Uuid $requestUuid, \DateTimeImmutable $when, int $driverId): void
    {
        $this->loadByUuid($requestUuid)->acceptedAt($when, $driverId);
    }

    public function transitCancelled(Uuid $requestUuid): void
    {
        $this->loadByUuid($requestUuid)->cancelled();
    }

    public function transitCompleted(Uuid $requestUuid, \DateTimeImmutable $when, Money $price, Money $driverFee): void
    {
        $this->loadByUuid($requestUuid)->completedAt($when, $price, $driverFee);
    }

    public function driversAreInvolved(Uuid $requestUuid, InvolvedDriversSummary $involvedDriversSummary): void
    {
        $this->loadByUuid($requestUuid)->involvedDriversAre($involvedDriversSummary);
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

    private function loadByUuid(Uuid $requestUuid): TransitDetails
    {
        return $this->transitDetailsRepository->findByRequestUuid($requestUuid);
    }
}
