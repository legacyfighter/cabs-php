<?php

namespace LegacyFighter\Cabs\Ride;

use LegacyFighter\Cabs\Crm\Claims\ClaimDTO;
use LegacyFighter\Cabs\Crm\ClientDTO;
use LegacyFighter\Cabs\DriverFleet\DriverDTO;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Ride\Details\TransitDetailsDTO;
use Symfony\Component\Uid\Uuid;

class TransitDTO implements \JsonSerializable
{
    private ?int $id;
    private Uuid $requestUuid;
    private string $tariff;
    private string $status;
    public ?DriverDTO $driver = null;
    public ?int $factor;
    private ?Distance $distance;
    private string $distanceUnit;
    private float $kmRate;
    private ?float $price = null;
    private ?float $driverFee = null;
    private ?float $estimatedPrice = null;
    private float $baseFee;
    private \DateTimeImmutable $date;
    private ?\DateTimeImmutable $dateTime;
    private ?\DateTimeImmutable $published;
    private ?\DateTimeImmutable $acceptedAt;
    private ?\DateTimeImmutable $started;
    private ?\DateTimeImmutable $completedAt;
    private ?ClaimDTO $claimDTO = null;
    /**
     * @var DriverDTO[]
     */
    private array $proposedDrivers = [];
    private AddressDTO $to;
    private AddressDTO $from;
    private ?string $carClass = null;
    private ?ClientDTO $clientDTO = null;

    private function __construct()
    {

    }

    public static function with(int $id, Uuid $requestUuid, string $status, string $tariff, float $kmRate, AddressDTO $from, AddressDTO $to, ?DriverDTO $driverDTO, ?ClientDTO $clientDTO, ?ClaimDTO $claimDTO, ?string $carType = null): self
    {
        $instance = new self();
        $instance->id = $id;
        $instance->requestUuid = $requestUuid;
        $instance->status = $status;
        $instance->tariff = $tariff;
        $instance->kmRate = $kmRate;
        $instance->from = $from;
        $instance->to = $to;
        $instance->driver = $driverDTO;
        $instance->clientDTO = $clientDTO;
        $instance->claimDTO = $claimDTO;
        $instance->carClass = $carType;
        return $instance;
    }

    /**
     * @param DriverDTO[] $proposedDrivers
     */
    public static function from(TransitDetailsDTO $transitDetails, array $proposedDrivers, ?int $assignedDriver = null): self
    {
        $instance = new self();
        $instance->id = $transitDetails->transitId;
        $instance->requestUuid = $transitDetails->requestUuid;
        $instance->distance = $transitDetails->distance;
        $instance->factor = 1;
        $instance->price = $transitDetails->price !== null ? (float) $transitDetails->price->toInt() : null;
        $instance->date = $transitDetails->dateTime;
        $instance->status = $transitDetails->status;
        $instance->tariff = $transitDetails->tariffName;
        $instance->kmRate = $transitDetails->kmRate;
        $instance->baseFee = $transitDetails->baseFee;
        $driver = array_values(array_filter($proposedDrivers, fn(DriverDTO $d) => $d->getId() === $assignedDriver));
        $instance->driver = $driver !== [] ? $driver[0] : null;
        $instance->proposedDrivers = $proposedDrivers;
        $instance->to = $transitDetails->to;
        $instance->from = $transitDetails->from;
        $instance->carClass = $transitDetails->carType;
        $instance->clientDTO = $transitDetails->client;
        $instance->driverFee = $transitDetails->driverFee !== null ? (float) $transitDetails->driverFee->toInt() : null;
        $instance->estimatedPrice = $transitDetails->estimatedPrice !== null ? (float) $transitDetails->estimatedPrice->toInt() : null;
        $instance->dateTime = $transitDetails->dateTime;
        $instance->published = $transitDetails->publishedAt;
        $instance->acceptedAt = $transitDetails->acceptedAt;
        $instance->started = $transitDetails->started;
        $instance->completedAt = $transitDetails->completedAt;
        return $instance;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTariff(): string
    {
        return $this->tariff;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDistanceUnit(): string
    {
        return $this->distanceUnit;
    }

    public function getKmRate(): float
    {
        return $this->kmRate;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getDistance(string $unit): string
    {
        $this->distanceUnit = $unit;
        return $this->distance->printIn($unit);
    }

    public function getDriverFee(): ?float
    {
        return $this->driverFee;
    }

    public function getEstimatedPrice(): ?float
    {
        return $this->estimatedPrice;
    }

    public function getBaseFee(): float
    {
        return $this->baseFee;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getDateTime(): ?\DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function getPublished(): ?\DateTimeImmutable
    {
        return $this->published;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function getStarted(): ?\DateTimeImmutable
    {
        return $this->started;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getClaimDTO(): ?ClaimDTO
    {
        return $this->claimDTO;
    }

    public function getProposedDrivers(): array
    {
        return $this->proposedDrivers;
    }

    public function getTo(): AddressDTO
    {
        return $this->to;
    }

    public function getFrom(): AddressDTO
    {
        return $this->from;
    }

    public function getCarClass(): ?string
    {
        return $this->carClass;
    }

    public function getClientDTO(): ClientDTO
    {
        return $this->clientDTO;
    }

    public function setClaimDTO(?ClaimDTO $claimDTO): void
    {
        $this->claimDTO = $claimDTO;
    }

    public function getRequestUuid(): Uuid
    {
        return $this->requestUuid;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'requestUuid' => (string) $this->requestUuid,
            'status' => $this->status,
            'tariff' => $this->getTariff(),
            'kmRate' => $this->kmRate,
            'from' => $this->from,
            'to' => $this->to,
            'driver' => $this->driver,
            'client' => $this->clientDTO,
            'claim' => $this->claimDTO
        ];
    }
}
