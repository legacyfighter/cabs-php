<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\Crm\ClientRepository;

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
