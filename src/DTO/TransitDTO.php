<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\Entity\Transit;

class TransitDTO implements \JsonSerializable
{
    private int $id;
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
    private ?string $carClass;
    private ?ClientDTO $clientDTO = null;

    private function __construct(Transit $transit)
    {
        $this->id = $transit->getId();
        $this->distance = $transit->getKm();
        $this->factor = 1;
        if($transit->getPrice()!== null) {
            $this->price = (float) $transit->getPrice()->toInt();
        }
        $this->date = $transit->getDateTime();
        $this->status = $transit->getStatus();
        $this->setTariff($transit);
        foreach ($transit->getProposedDrivers() as $driver) {
            $this->proposedDrivers[] = new DriverDTO($driver);
        }
        $this->to = AddressDTO::from($transit->getTo());
        $this->from = AddressDTO::from($transit->getFrom());
        $this->carClass = $transit->getCarType();
        $this->clientDTO = ClientDTO::from($transit->getClient());
        if($transit->getDriversFee()!==null) {
            $this->driverFee = (float) $transit->getDriversFee()->toInt();
        }
        if($transit->getEstimatedPrice() !== null) {
            $this->estimatedPrice = (float) $transit->getEstimatedPrice()->toInt();
        }
        $this->dateTime = $transit->getDateTime();
        $this->published = $transit->getPublished();
        $this->acceptedAt = $transit->getAcceptedAt();
        $this->started = $transit->getStarted();
        $this->completedAt = $transit->getCompleteAt();
    }

    public static function from(Transit $transit): self
    {
        return new self($transit);
    }

    private function setTariff(Transit $transit): void
    {
        $this->tariff = $transit->getTariff()->getName();
        $this->kmRate = $transit->getTariff()->getKmRate();
        $this->baseFee = $transit->getTariff()->getBaseFee();
    }

    public function getId(): int
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDistance(string $unit): string
    {
        $this->distanceUnit = $unit;
        return $this->distance->printIn($unit);
    }

    public function getDriverFee(): float
    {
        return $this->driverFee;
    }

    public function getEstimatedPrice(): float
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

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function getPublished(): \DateTimeImmutable
    {
        return $this->published;
    }

    public function getAcceptedAt(): \DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function getStarted(): \DateTimeImmutable
    {
        return $this->started;
    }

    public function getCompletedAt(): \DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getClaimDTO(): ClaimDTO
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

    public function getCarClass(): string
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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
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
