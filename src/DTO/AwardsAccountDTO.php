<?php

namespace LegacyFighter\Cabs\DTO;

use LegacyFighter\Cabs\Entity\AwardsAccount;

class AwardsAccountDTO implements \JsonSerializable
{
    private ClientDTO $clientDTO;
    private \DateTimeImmutable $date;
    private bool $isActive;
    private int $transactions;

    private function __construct(AwardsAccount $account)
    {
        $this->clientDTO = ClientDTO::from($account->getClient());
        $this->date = $account->getDate();
        $this->isActive = $account->isActive();
        $this->transactions = $account->getTransactions();
    }

    public static function from(AwardsAccount $account): self
    {
        return new self($account);
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
