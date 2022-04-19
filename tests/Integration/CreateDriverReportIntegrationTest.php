<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\CarFleet\CarType;
use LegacyFighter\Cabs\CarFleet\CarTypeService;
use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\DriverFleet\DriverAttribute;
use LegacyFighter\Cabs\DriverFleet\DriverFee;
use LegacyFighter\Cabs\DriverFleet\DriverReport\DriverReportController;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Geolocation\Address\Address;
use LegacyFighter\Cabs\Geolocation\Address\AddressRepository;
use LegacyFighter\Cabs\Geolocation\GeocodingService;
use LegacyFighter\Cabs\Service\DriverSessionService;
use LegacyFighter\Cabs\Service\DriverTrackingService;
use LegacyFighter\Cabs\Service\TransitService;
use LegacyFighter\Cabs\Tests\Common\FixedClock;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use LegacyFighter\Cabs\Tests\Double\FakeGeocodingService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class CreateDriverReportIntegrationTest extends KernelTestCase
{
    private TransitService $transitService;
    private DriverTrackingService $driverTrackingService;
    private DriverSessionService $driverSessionService;
    private CarTypeService $carTypeService;
    private Fixtures $fixtures;
    private DriverReportController $driverReportController;
    private AddressRepository $addressRepository;
    private FakeGeocodingService $geocodingService;
    private FixedClock $clock;

    protected function setUp(): void
    {
        $this->transitService = $this->getContainer()->get(TransitService::class);
        $this->driverTrackingService = $this->getContainer()->get(DriverTrackingService::class);
        $this->driverSessionService = $this->getContainer()->get(DriverSessionService::class);
        $this->carTypeService = $this->getContainer()->get(CarTypeService::class);
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
        $this->driverReportController = $this->getContainer()->get(DriverReportController::class);
        $this->addressRepository = $this->getContainer()->get(AddressRepository::class);
        $this->geocodingService = $this->getContainer()->get(GeocodingService::class);
        $this->clock = $this->getContainer()->get(Clock::class);

        $this->anActiveCarCategory(CarType::CAR_CLASS_VAN);
        $this->anActiveCarCategory(CarType::CAR_CLASS_PREMIUM);
    }

    /**
     * @test
     */
    public function shouldCreateDriversReport(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $driver = $this->aDriver(Driver::STATUS_ACTIVE, 'Jan', 'Nowak', 'FARME100165AB5EW');
        //and
        $this->fixtures->driverHasAttribute($driver, DriverAttribute::NAME_COMPANY_NAME, 'UBER');
        $this->fixtures->driverHasAttribute($driver, DriverAttribute::NAME_PENALTY_POINTS, '21');
        $this->fixtures->driverHasAttribute($driver, DriverAttribute::NAME_MEDICAL_EXAMINATION_REMARKS, 'private info');
        //and
        $this->driverHasDoneSessionAndPicksSomeoneUpInCar($driver, $client, CarType::CAR_CLASS_VAN, 'WU1213', 'SCODA FABIA', $this->today());
        $this->driverHasDoneSessionAndPicksSomeoneUpInCar($driver, $client, CarType::CAR_CLASS_VAN, 'WU1213', 'SCODA OCTAVIA', $this->yesterday());
        $inBmw = $this->driverHasDoneSessionAndPicksSomeoneUpInCar($driver, $client, CarType::CAR_CLASS_VAN, 'WU1213', 'BMW M2', $this->dayBeforeYesterday());
        //and
        $this->fixtures->createClaim($client, $inBmw, 'za szybko');
        $this->getContainer()->get(EntityManagerInterface::class)->clear();

        //when
        $driverReportWithin2days = $this->loadReportIncludingPastDays($driver, 2);
        $driverReportWithin1day = $this->loadReportIncludingPastDays($driver, 1);
        $driverReportForJustToday = $this->loadReportIncludingPastDays($driver, 0);

        //then
        self::assertCount(3, $driverReportWithin2days['sessions']);
        self::assertCount(2, $driverReportWithin1day['sessions']);
        self::assertCount(1, $driverReportForJustToday['sessions']);

        self::assertSame('FARME100165AB5EW',$driverReportWithin2days['driver']['driverLicense']);
        self::assertSame('Jan',$driverReportWithin2days['driver']['firstName']);
        self::assertSame('Nowak',$driverReportWithin2days['driver']['lastName']);
        self::assertCount(2, $driverReportWithin2days['attributes']);
        self::assertContains(['name' => DriverAttribute::NAME_COMPANY_NAME, 'value' => 'UBER'], $driverReportWithin2days['attributes']);
        self::assertContains(['name' => DriverAttribute::NAME_PENALTY_POINTS, 'value' => '21'], $driverReportWithin2days['attributes']);

        self::assertCount(1, $this->transitsInSessionIn('SCODA FABIA', $driverReportWithin2days));
        self::assertNull($this->transitsInSessionIn('SCODA FABIA', $driverReportWithin2days)[0]['claim']);

        self::assertCount(1, $this->transitsInSessionIn('SCODA OCTAVIA', $driverReportWithin2days));
        self::assertNull($this->transitsInSessionIn('SCODA OCTAVIA', $driverReportWithin2days)[0]['claim']);

        self::assertCount(1, $this->transitsInSessionIn('BMW M2', $driverReportWithin2days));
        self::assertNotNull($this->transitsInSessionIn('BMW M2', $driverReportWithin2days)[0]['claim']);
        self::assertEquals('za szybko', $this->transitsInSessionIn('BMW M2', $driverReportWithin2days)[0]['claim']['reason']);
    }

    private function transitsInSessionIn(string $carBrand, array $report): array
    {
        return array_values(array_filter($report['sessions'], fn(array $session) => $session['session']['carBrand'] === $carBrand))[0]['transits'];
    }

    private function driverHasDoneSessionAndPicksSomeoneUpInCar(
        Driver $driver,
        Client $client,
        string $carClass,
        string $plateNumber,
        string $carBrand,
        \DateTimeImmutable $when
    ): Transit
    {
        $this->clock->setDateTime($when);
        $this->driverSessionService->logIn($driver->getId(), $plateNumber, $carClass, $carBrand);
        $this->driverTrackingService->registerPosition($driver->getId(), 10, 20, new \DateTimeImmutable());
        $transit = $this->transitService->createTransitFrom(
            $client->getId(),
            $this->address('PL', 'MAZ', 'WAW', 'STREET', 1, 10, 20),
            $this->address('PL', 'MAZ', 'WAW', 'STREET', 100, 10.01, 20.01),
            $carClass
        );
        $this->transitService->publishTransit($transit->getId());
        $this->transitService->acceptTransit($driver->getId(), $transit->getId());
        $this->transitService->startTransit($driver->getId(), $transit->getId());
        $this->transitService->completeTransitFrom($driver->getId(), $transit->getId(), $this->address('PL', 'MAZ', 'WAW', 'STREET', 100, 10.01, 20.01));
        $this->driverSessionService->logOutCurrentSession($driver->getId());
        return $transit;
    }

    private function loadReportIncludingPastDays(Driver $driver, int $days): array
    {
        $this->clock->setDateTime($this->today());
        $response = $this->driverReportController->loadReportForDriver($driver->getId(), Request::create('uri', 'GET', ['lastDays' => $days]));
        return json_decode($response->getContent(), true);
    }

    private function aDriver(string $status, string $name, string $lastName, string $license): Driver
    {
        $driver = $this->fixtures->aDriver($status, $name, $lastName, $license);
        $this->fixtures->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);
        return $driver;
    }

    private function address(string $country, string $district, string $city, string $street, int $buildingNumber, float $latitude, float $longitude): Address
    {
        $address = new Address($country, $city, $street, $buildingNumber);
        $address->setDistrict($district);
        $address->setPostalCode('11-111');
        $address->setName('name');
        $address = $this->addressRepository->save($address);
        $this->geocodingService->setValuesForAddress($address, [$latitude, $longitude]);
        return $address;
    }

    private function anActiveCarCategory(string $carClass): void
    {
        $this->fixtures->anActiveCarCategory($carClass);
        foreach (range(1, 3) as $_) {
            $this->carTypeService->registerCar($carClass);
        }
    }

    private function dayBeforeYesterday(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('1989-12-12 12:12');
    }

    private function yesterday(): \DateTimeImmutable
    {
        return $this->dayBeforeYesterday()->modify('+1 day');
    }

    private function today(): \DateTimeImmutable
    {
        return $this->yesterday()->modify('+1 day');
    }
}
