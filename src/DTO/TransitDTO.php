<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\Transit;

class TransitDTO implements \JsonSerializable
{
    private int $id;
    private string $tariff;
    private string $status;
    public ?DriverDTO $driver = null;
    public ?int $factor;
    private ?float $distance;
    private string $distanceUnit;
    private float $kmRate;
    private ?float $price;
    private ?float $driverFee;
    private ?float $estimatedPrice;
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
        $this->factor = $transit->getFactor();
        $this->price = $transit->getPrice();
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
        $this->driverFee = $transit->getDriversFee();
        $this->estimatedPrice = $transit->getEstimatedPrice();
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
        $date = new \DateTimeImmutable();

        // wprowadzenie nowych cennikow od 1.01.2019
        if((int) $date->format('Y') <= 2018) {
            $this->kmRate = 1.0;
            $this->tariff = 'Standard';
            return;
        }

        $year = (int) $date->format('Y');
        $leap = (($year % 4 === 0) && ($year % 100 !== 0)) || ($year % 400 === 0);

        if(($leap && (int) $date->format('z') === 365) || (!$leap && (int) $date->format('z') === 364) || ((int) $date->format('z') === 0 && (int) $date->format('H') < 6)) {
            $this->tariff = 'Sylwester';
            $this->kmRate = 3.5;
        } else {
            switch ((int) $date->format('l')) {
                case 'Monday':
                case 'Tuesday':
                case 'Wednesday':
                case 'Thursday':
                    $this->kmRate = 1.0;
                    $this->tariff = 'Standard';
                    break;
                case 'Friday':
                    if((int) $date->format('H') < 17) {
                        $this->tariff = 'Standard';
                        $this->kmRate = 1.0;
                    } else {
                        $this->tariff = 'Weekend+';
                        $this->kmRate = 2.5;
                    }
                    break;
                case 'Saturday':
                    if((int) $date->format('H') < 6 || (int) $date->format('H') >= 17) {
                        $this->kmRate = 2.5;
                        $this->tariff = 'Weekend+';
                    } else if ((int) $date->format('H') < 17) {
                        $this->kmRate = 1.5;
                        $this->tariff = 'Weekend';
                    }
                    break;
                case 'Sunday':
                    if((int) $date->format('H') < 6) {
                        $this->kmRate = 2.5;
                        $this->tariff = 'Weekend+';
                    } else {
                        $this->kmRate = 1.5;
                        $this->tariff = 'Weekend';
                    }
                    break;
            }
        }
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
        if($unit === 'km') {
            if($this->distance === ceil($this->distance)) {
                return sprintf('%d', round($this->distance)).'km';
            }
            return sprintf('%.3f', $this->distance).'km';
        }
        if($unit === 'miles') {
            $distance = $this->distance / 1.609344;
            if($distance === ceil($distance)) {
                return sprintf('%d', round($distance)).'miles';
            }
            return sprintf('%.3f', $distance).'miles';
        }
        if($unit === 'm') {
            return sprintf('%d', round($this->distance * 1000)).'m';
        }
        throw new \InvalidArgumentException('Invalid unit '.$unit);
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
