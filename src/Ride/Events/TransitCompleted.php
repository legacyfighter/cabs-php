<?php

namespace LegacyFighter\Cabs\Ride\Events;

class TransitCompleted
{
    public function __construct(
        private int $clientId,
        private int $transitId,
        private int $addressFromHash,
        private int $addressToHash,
        private \DateTimeImmutable $started,
        private \DateTimeImmutable $completeAt
    ) {}

    public function clientId(): int
    {
        return $this->clientId;
    }

    public function transitId(): int
    {
        return $this->transitId;
    }

    public function addressFromHash(): int
    {
        return $this->addressFromHash;
    }

    public function addressToHash(): int
    {
        return $this->addressToHash;
    }

    public function started(): \DateTimeImmutable
    {
        return $this->started;
    }

    public function completeAt(): \DateTimeImmutable
    {
        return $this->completeAt;
    }
}
