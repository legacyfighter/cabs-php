<?php

namespace LegacyFighter\Cabs\Tests\Common;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\CarFleet\CarType;
use LegacyFighter\Cabs\CarFleet\CarTypeDTO;
use LegacyFighter\Cabs\Crm\Claims\Claim;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\DriverFleet\DriverFee;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Pricing\Tariff;
use LegacyFighter\Cabs\Ride\Details\TransitDetails;
use LegacyFighter\Cabs\Ride\Details\TransitDetailsDTO;
use LegacyFighter\Cabs\Ride\Transit;
use LegacyFighter\Cabs\Ride\TransitDTO;
use Symfony\Component\Uid\Uuid;

class Fixtures
{
    public function __construct(
        private AddressFixture $addressFixture,
        private AwardsAccountFixture $awardsAccountFixture,
        private CarTypeFixture $carTypeFixture,
        private ClaimFixture $claimFixture,
        private ClientFixture $clientFixture,
        private DriverFixture $driverFixture,
        private RideFixture $rideFixture,
        private TransitFixture $transitFixture,
        private EntityManagerInterface $em
    )
    {
    }

    public function anAddress(string $country = 'Polska', string $city = 'Warszawa', string $street = 'Młynarska', int $buildingNumber = 20): Address
    {
        return $this->addressFixture->anAddress($country, $city, $street, $buildingNumber);
    }

    public function aClient(string $type = Client::TYPE_NORMAL): Client
    {
        return $this->clientFixture->aClient($type);
    }

    public function aDriver(
        string $status = Driver::STATUS_ACTIVE,
        string $name = 'Janusz',
        string $lastName = 'Kowalski',
        string $license = 'FARME100165AB5EW'
    ): Driver
    {
        return $this->driverFixture->aDriver($status, $name, $lastName, $license);
    }

    public function aNearbyDriver(string $plateNumber = 'WU DAMIAN'): Driver
    {
        return $this->driverFixture->aNearbyDriver($plateNumber);
    }

    public function driverHasFee(Driver $driver, string $feeType, int $amount, ?int $min = null): DriverFee
    {
        return $this->driverFixture->driverHasFee($driver, $feeType, $amount, $min);
    }

    public function transitDetails(Driver $driver, int $price, \DateTimeImmutable $when, ?Client $client = null): Transit
    {
        return $this->transitFixture->transitDetails($driver, $price, $when, $client ?? $this->aClient(), $this->anAddress(), $this->anAddress());
    }

    public function anActiveCarCategory(string $carClass): CarTypeDTO
    {
        return $this->carTypeFixture->anActiveCarCategory($carClass);
    }

    public function aTransitDTOWith(Client $client, AddressDTO $from, AddressDTO $to): TransitDTO
    {
        $transitDetails = new TransitDetails(new \DateTimeImmutable(), Uuid::v4(), $from->toAddressEntity(), $to->toAddressEntity(), Distance::zero(), $client, CarType::CAR_CLASS_VAN, Money::zero(), Tariff::of(0, 'fake', Money::from(20)));
        PrivateProperty::setId(1, $transitDetails);

        return TransitDTO::from(TransitDetailsDTO::from($transitDetails), []);
    }

    public function aTransitDTO(AddressDTO $from, AddressDTO $to): TransitDTO
    {
        return $this->aTransitDTOWith($this->aClient(), $from, $to);
    }

    public function clientHasDoneTransits(Client $client, int $noOfTransits): void
    {
        foreach (range(1, $noOfTransits) as $_) {
            $this->aRide(10, $client, $this->aNearbyDriver(), $this->anAddress(), $this->anAddress());
        }
    }

    public function aRide(int $price, Client $client, Driver $driver, Address $from, Address $destination): TransitDTO
    {
        return $this->rideFixture->aRide($price, $client, $driver, $from, $destination);
    }

    public function aRideWithFixedClock(int $price, \DateTimeImmutable $publishedAt, \DateTimeImmutable $completedAt, Client $client, Driver $driver, Address $from, Address $destination, FixedClock $clock): void
    {
        $this->rideFixture->aRideWithFixedClock($price, $publishedAt, $completedAt, $client, $driver, $from, $destination, $clock);
    }

    public function createClaim(Client $client, TransitDTO $transit, string $reason = '$$$'): Claim
    {
        return $this->claimFixture->createClaim($client, $transit, $reason);
    }

    public function createAndResolveClaim(Client $client, TransitDTO $transit): Claim
    {
        return $this->claimFixture->createAndResolveClaim($client, $transit);
    }

    public function clientHasDoneClaims(Client $client, int $howMany): void
    {
        foreach (range(1, $howMany) as $_) {
            $this->createAndResolveClaim($client, $this->aRide(20, $client, $this->aNearbyDriver(), $this->anAddress(), $this->anAddress()));
        }
        $this->em->refresh($client);
    }

    public function aClientWithClaims(string $type, int $howManyClaims): Client
    {
        $client = $this->aClient($type);
        $this->awardsAccount($client);
        $this->clientHasDoneClaims($client, $howManyClaims);
        return $client;
    }

    public function anAddressDTO(string $country, string $city, string $street, int $buildingNumber): AddressDTO
    {
        $address = new Address($country, $city, $street, $buildingNumber);
        $address->setPostalCode('11-111');
        $address->setName('name');
        $address->setDistrict('district');
        return AddressDTO::from($address);
    }

    public function awardsAccount(Client $client): void
    {
        $this->awardsAccountFixture->awardsAccount($client);
    }

    public function activeAwardsAccount(Client $client): void
    {
        $this->awardsAccountFixture->activeAwardsAccount($client);
    }

    public function driverHasAttribute(Driver $driver, string $name, string $value): void
    {
        $this->driverFixture->driverHasAttribute($driver, $name, $value);
    }
}
