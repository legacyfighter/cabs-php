<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\Miles\AwardsAccount;

class AwardsAccountDTO implements \JsonSerializable
{
    private ClientDTO $clientDTO;
    private \DateTimeImmutable $date;
    private bool $isActive;
    private int $transactions;

    private function __construct(AwardsAccount $account, ClientDTO $clientDTO)
    {
        $this->clientDTO = $clientDTO;
        $this->date = $account->getDate();
        $this->isActive = $account->isActive();
        $this->transactions = $account->getTransactions();
    }

    public static function from(AwardsAccount $account, ClientDTO $clientDTO): self
    {
        return new self($account, $clientDTO);
    }

    public function getClient(): ClientDTO
    {
        return $this->clientDTO;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getTransactions(): int
    {
        return $this->transactions;
    }

    public function jsonSerialize(): array
    {
        return [
            'client' => $this->clientDTO,
            'date' => $this->date->format('Y-m-d H:i:s'),
            'active' => $this->isActive,
            'transactions' => $this->transactions
        ];
    }


}
