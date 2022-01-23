<?php

namespace LegacyFighter\Cabs\Tests\Common;

use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverFee;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repository\AddressRepository;
use LegacyFighter\Cabs\Repository\ClientRepository;
use LegacyFighter\Cabs\Repository\DriverFeeRepository;
use LegacyFighter\Cabs\Repository\TransitRepository;
use LegacyFighter\Cabs\Service\DriverService;

class Fixtures
{
    private TransitRepository $transitRepository;
    private DriverFeeRepository $feeRepository;
    private DriverService $driverService;
    private AddressRepository $addressRepository;
    private ClientRepository $clientRepository;

    public function __construct(
        TransitRepository $transitRepository,
        DriverFeeRepository $feeRepository,
        DriverService $driverService,
        AddressRepository $addressRepository,
        ClientRepository $clientRepository
    )
    {
        $this->transitRepository = $transitRepository;
        $this->feeRepository = $feeRepository;
        $this->driverService = $driverService;
        $this->addressRepository = $addressRepository;
        $this->clientRepository = $clientRepository;
    }


    public function aClient(): Client
    {
        $client = new Client();
        $client->setName('Janusz');
        $client->setLastName('Kowalski');
        $client->setType(Client::TYPE_NORMAL);
        $client->setDefaultPaymentType(Client::PAYMENT_TYPE_MONTHLY_INVOICE);
        return $this->clientRepository->save($client);
    }

    public function aDriver(): Driver
    {
        return $this->driverService->createDriver('FARME100165AB5EW', 'Kowalski', 'Janusz', Driver::TYPE_REGULAR, Driver::STATUS_ACTIVE, '');
    }

    public function driverHasFee(Driver $driver, string $feeType, int $amount, ?int $min = null): DriverFee
    {
        $driverFee = new DriverFee($feeType, $driver, $amount, $min === null ? Money::zero() : Money::from($min));
        return $this->feeRepository->save($driverFee);
    }

    public function aTransit(?Driver $driver, int $price, ?\DateTimeImmutable $when = null): Transit
    {
        $transit = new Transit();
        $transit->setStatus(Transit::STATUS_DRAFT);
        $transit->setPrice(Money::from($price));
        $transit->setDriver($driver);
        $transit->setDateTime($when ?? new \DateTimeImmutable());
        return $this->transitRepository->save($transit);
    }

    public function aCompletedTransitAt(int $price, \DateTimeImmutable $when): Transit
    {
        $transit = $this->aTransit(null, $price, $when);
        $transit->setTo($this->anAddress('Polska', 'Warszawa', 'Zytnia', 20));
        $transit->setFrom($this->anAddress('Polska', 'Warszawa', 'Młynarska', 20));
        $transit->setClient($this->aClient());
        return $this->transitRepository->save($transit);
    }

    private function anAddress(string $country, string $city, string $street, int $buildingNumber): Address
    {
        $address = new Address($country, $city, $street, $buildingNumber);
        $address->setPostalCode('11-111');
        $address->setName('Home');
        $address->setDistrict('district');
        return $this->addressRepository->save($address);
    }
}
