<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\DTO\ClientDTO;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Repository\ClientRepository;

class ClientService
{
    private ClientRepository $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function registerClient(string $name, string $lastName, string $type, string $paymentType): Client
    {
        $client = new Client();
        $client->setName($name);
        $client->setLastName($lastName);
        $client->setType($type);
        $client->setDefaultPaymentType($paymentType);
        return $this->clientRepository->save($client);
    }

    public function changeDefaultPaymentType(int $clientId, string $paymentType): void
    {
        $client = $this->clientRepository->getOne($clientId);
        if($client==null) {
            throw new \InvalidArgumentException('Client does not exists, id = '.$clientId);
        }
        $client->setDefaultPaymentType($paymentType);
        $this->clientRepository->save($client);
    }

    public function upgradeToVIP(int $clientId): void
    {
        $client = $this->clientRepository->getOne($clientId);
        if($client==null) {
            throw new \InvalidArgumentException('Client does not exists, id = '.$clientId);
        }
        $client->setType(Client::TYPE_VIP);
        $this->clientRepository->save($client);
    }

    public function downgradeToRegular(int $clientId): void
    {
        $client = $this->clientRepository->getOne($clientId);
        if($client==null) {
            throw new \InvalidArgumentException('Client does not exists, id = '.$clientId);
        }
        $client->setType(Client::TYPE_NORMAL);
        $this->clientRepository->save($client);
    }

    public function load(int $clientId): ClientDTO
    {
        return ClientDTO::from($this->clientRepository->getOne($clientId));
    }
}
