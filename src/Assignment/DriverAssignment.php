<?php

namespace LegacyFighter\Cabs\Assignment;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;
use Symfony\Component\Uid\Uuid;

#[Entity]
class DriverAssignment extends BaseEntity
{
    #[Column(type: 'uuid')]
    private Uuid $requestId;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $publishedAt;

    #[Column]
    private string $status = AssignmentStatus::WAITING_FOR_DRIVER_ASSIGNMENT;

    #[Column(type: 'integer', nullable: true)]
    private ?int $assignedDriver = null;

    #[Column(type: 'json')]
    private array $driversRejections = [];

    #[Column(type: 'json')]
    private array $proposedDrivers = [];

    #[Column(type: 'integer')]
    private int $awaitingDriversResponses = 0;

    public function __construct(Uuid $requestId, \DateTimeImmutable $publishedAt)
    {
        $this->requestId = $requestId;
        $this->publishedAt = $publishedAt;
    }

    public function cancel(): void
    {
        if(!in_array($this->status, [AssignmentStatus::WAITING_FOR_DRIVER_ASSIGNMENT, AssignmentStatus::ON_THE_WAY], true)) {
            throw new \RuntimeException('Transit cannot be cancelled, id = '.$this->getRequestId());
        }
        $this->status = AssignmentStatus::CANCELLED;
        $this->assignedDriver = null;
        $this->awaitingDriversResponses = 0;
    }

    public function canProposeTo(int $driverId): bool
    {
        return !in_array($driverId, $this->driversRejections);
    }

    public function proposeTo(int $driverId): void
    {
        if($this->canProposeTo($driverId)) {
            $this->proposedDrivers[] = $driverId;
            $this->awaitingDriversResponses++;
        }
    }

    public function failDriverAssignment(): void
    {
        $this->status = AssignmentStatus::DRIVER_ASSIGNMENT_FAILED;
        $this->assignedDriver = null;
        $this->awaitingDriversResponses = 0;
    }

    public function shouldNotWaitForDriverAnyMore(\DateTimeImmutable $date): bool
    {
        return $this->status === AssignmentStatus::CANCELLED || $this->publishedAt->modify('+300 seconds') < $date;
    }

    public function acceptBy(int $driverId): void
    {
        if($this->assignedDriver !== null) {
            throw new \RuntimeException('Transit already accepted, id = '.$this->getRequestId());
        }
        if(!in_array($driverId, $this->proposedDrivers)) {
            throw new \RuntimeException('Driver out of possible drivers, id = '.$this->getRequestId());
        }
        if(in_array($driverId, $this->driversRejections)) {
            throw new \RuntimeException('Driver out of possible drivers, id = '.$this->getRequestId());
        }

        $this->assignedDriver = $driverId;
        $this->awaitingDriversResponses = 0;
        $this->status = AssignmentStatus::ON_THE_WAY;
    }

    public function rejectBy(int $driverId): void
    {
        $this->driversRejections[] = $driverId;
        $this->awaitingDriversResponses--;
    }

    public function getRequestId(): Uuid
    {
        return $this->requestId;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAssignedDriver(): ?int
    {
        return $this->assignedDriver;
    }

    public function getDriversRejections(): array
    {
        return $this->driversRejections;
    }

    public function getProposedDrivers(): array
    {
        return $this->proposedDrivers;
    }

    public function getAwaitingDriversResponses(): int
    {
        return $this->awaitingDriversResponses;
    }
}
