<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Repository\ClientRepository;

class ClientFixture
{
    public function __construct(
        private ClientRepository $clientRepository
    )
    {
    }

    public function aClient(string $type = Client::TYPE_NORMAL): Client
    {
        $client = new Client();
        $client->setName('Janusz');
        $client->setLastName('Kowalski');
        $client->setType($type);
        $client->setDefaultPaymentType(Client::PAYMENT_TYPE_MONTHLY_INVOICE);
        return $this->clientRepository->save($client);
    }
}
