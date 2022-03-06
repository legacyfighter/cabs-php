<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\CarType;
use LegacyFighter\Cabs\Service\GeocodingService;
use LegacyFighter\Cabs\Tests\Common\FixedClock;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use LegacyFighter\Cabs\Tests\Double\FakeGeocodingService;
use LegacyFighter\Cabs\Ui\TransitAnalyzerController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AnalyzeNearbyTransitsIntegrationTest extends KernelTestCase
{
    private Fixtures $fixtures;
    private TransitAnalyzerController $transitAnalyzerController;
    private FixedClock $clock;

    protected function setUp(): void
    {
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
        $this->transitAnalyzerController = $this->getContainer()->get(TransitAnalyzerController::class);
        $this->clock = $this->getContainer()->get(Clock::class);

        $this->fixtures->anActiveCarCategory(CarType::CAR_CLASS_VAN);
    }

    /**
     * @test
     */
    public function canFindLongestTravel(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->clock->setDateTime(new \DateTimeImmutable('2021-01-01 00:00'));
        $driver = $this->fixtures->aNearbyDriver('WA001');
        //and
        $address1 = new Address('1_1', '1', '1', 1);
        $address2 = new Address('1_2', '2', '2', 2);
        $address3 = new Address('1_3', '3', '3', 3);
        $address4 = new Address('1_4', '4', '4', 3);
        $address5 = new Address('1_5', '5', '5', 3);
        //and
        //1-2-3-4
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:00', '2021-01-01 00:10', $client, $driver, $address1, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:15', '2021-01-01 00:20', $client, $driver, $address2, $address3, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:25', '2021-01-01 00:30', $client, $driver, $address3, $address4, $this->clock);
        //1-2-3
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-02 00:00', '2021-01-02 00:10', $client, $driver, $address1, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-02 00:15', '2021-01-02 00:20', $client, $driver, $address2, $address3, $this->clock);
        //1-3
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-03 00:00', '2021-01-03 00:10', $client, $driver, $address1, $address3, $this->clock);
        //3-1-2-5-4-5
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:00', '2021-02-01 00:10', $client, $driver, $address3, $address1, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:20', '2021-02-01 00:25', $client, $driver, $address1, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:30', '2021-02-01 00:35', $client, $driver, $address2, $address5, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:40', '2021-02-01 00:45', $client, $driver, $address5, $address4, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:50', '2021-02-01 00:55', $client, $driver, $address4, $address5, $this->clock);

        //when
        $response = $this->transitAnalyzerController->analyze($client->getId(), $address1->getId());

        //then
        //1-2-5-4-5
        self::assertSame(
            [$address1->getHash(), $address2->getHash(), $address5->getHash(), $address4->getHash(), $address5->getHash()],
            $this->getHashes($response->getContent())
        );
    }

    /**
     * @test
     */
    public function canFindLongestTravelFromMultipleClients(): void
    {
        //given
        $client1 = $this->fixtures->aClient();
        $client2 = $this->fixtures->aClient();
        $client3 = $this->fixtures->aClient();
        $client4 = $this->fixtures->aClient();
        //and
        $this->clock->setDateTime(new \DateTimeImmutable('2021-01-01 00:00'));
        $driver = $this->fixtures->aNearbyDriver('WA001');
        //and
        $address1 = new Address('2_1', '1', '1', 1);
        $address2 = new Address('2_2', '2', '2', 2);
        $address3 = new Address('2_3', '3', '3', 3);
        $address4 = new Address('2_4', '4', '4', 3);
        $address5 = new Address('2_5', '5', '5', 3);
        //and
        //1-2-3-4
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:00', '2021-01-01 00:10', $client1, $driver, $address1, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:15', '2021-01-01 00:20', $client1, $driver, $address2, $address3, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:25', '2021-01-01 00:30', $client1, $driver, $address3, $address4, $this->clock);
        //1-2-3
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-02 00:00', '2021-01-02 00:10', $client2, $driver, $address1, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-02 00:15', '2021-01-02 00:20', $client2, $driver, $address2, $address3, $this->clock);
        //1-3
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-03 00:00', '2021-01-03 00:10', $client3, $driver, $address1, $address3, $this->clock);
        //3-1-2-5-4-5
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:00', '2021-02-01 00:10', $client4, $driver, $address3, $address1, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:20', '2021-02-01 00:25', $client4, $driver, $address1, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:30', '2021-02-01 00:35', $client4, $driver, $address2, $address5, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:40', '2021-02-01 00:45', $client4, $driver, $address5, $address4, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:50', '2021-02-01 00:55', $client4, $driver, $address4, $address5, $this->clock);

        //when
        $response = $this->transitAnalyzerController->analyze($client1->getId(), $address1->getId());

        //then
        //1-2-3-4
        self::assertSame(
            [$address1->getHash(), $address2->getHash(), $address3->getHash(), $address4->getHash()],
            $this->getHashes($response->getContent())
        );
    }

    /**
     * @test
     */
    public function canFindLongestTravelWithLongStops(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->clock->setDateTime(new \DateTimeImmutable('2021-01-01 00:00'));
        $driver = $this->fixtures->aNearbyDriver('WA001');
        //and
        $address1 = new Address('3_1', '1', '1', 1);
        $address2 = new Address('3_2', '2', '2', 2);
        $address3 = new Address('3_3', '3', '3', 3);
        $address4 = new Address('3_4', '4', '4', 3);
        $address5 = new Address('3_5', '5', '5', 3);
        //and
        //1-2-3-4-(stop)-5-1
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:00', '2021-01-01 00:05', $client, $driver, $address1, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:10', '2021-01-01 00:15', $client, $driver, $address2, $address3, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:20', '2021-01-01 00:25', $client, $driver, $address3, $address4, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 01:00', '2021-01-01 01:10', $client, $driver, $address4, $address5, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 01:10', '2021-01-01 01:15', $client, $driver, $address5, $address1, $this->clock);

        //when
        $response = $this->transitAnalyzerController->analyze($client->getId(), $address1->getId());

        //then
        //1-2-3-4
        self::assertSame(
            [$address1->getHash(), $address2->getHash(), $address3->getHash(), $address4->getHash()],
            $this->getHashes($response->getContent())
        );
    }

    /**
     * @test
     */
    public function canFindLongestTravelWithLoops(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->clock->setDateTime(new \DateTimeImmutable('2021-01-01 00:00'));
        $driver = $this->fixtures->aNearbyDriver('WA001');
        //and
        $address1 = new Address('4_1', '1', '1', 1);
        $address2 = new Address('4_2', '2', '2', 2);
        $address3 = new Address('4_3', '3', '3', 3);
        $address4 = new Address('4_4', '4', '4', 3);
        $address5 = new Address('4_5', '5', '5', 3);
        //and
        //5-1-2-3
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:00', '2021-01-01 00:05', $client, $driver, $address5, $address1, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:06', '2021-01-01 00:10', $client, $driver, $address1, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:15', '2021-01-01 00:20', $client, $driver, $address2, $address3, $this->clock);
        //3-2-1
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-02 00:00', '2021-01-02 00:10', $client, $driver, $address3, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-02 00:15', '2021-01-02 00:20', $client, $driver, $address2, $address1, $this->clock);
        //1-5
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-03 00:00', '2021-01-03 00:10', $client, $driver, $address1, $address5, $this->clock);
        //3-1-2-5-4-5
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:00', '2021-02-01 00:10', $client, $driver, $address3, $address1, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:20', '2021-02-01 00:25', $client, $driver, $address1, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:30', '2021-02-01 00:35', $client, $driver, $address2, $address5, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:40', '2021-02-01 00:45', $client, $driver, $address5, $address4, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-02-01 00:50', '2021-02-01 00:55', $client, $driver, $address4, $address5, $this->clock);

        //when
        $response = $this->transitAnalyzerController->analyze($client->getId(), $address5->getId());

        //then
        //5-1-2-3
        self::assertSame(
            [$address5->getHash(), $address1->getHash(), $address2->getHash(), $address3->getHash()],
            $this->getHashes($response->getContent())
        );
    }

    /**
     * @test
     * pytanie za 100 punktów, czy ten test będzie działał na grafie, bo tam jest warunek na ścieżkę o długości przynajmniej 1...
     */
    public function canFindLongTravelBetweenOthers(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->clock->setDateTime(new \DateTimeImmutable('2021-01-01 00:00'));
        $driver = $this->fixtures->aNearbyDriver('WA001');
        //and
        $address1 = new Address('5_1', '1', '1', 1);
        $address2 = new Address('5_2', '2', '2', 2);
        $address3 = new Address('5_3', '3', '3', 3);
        $address4 = new Address('5_4', '4', '4', 3);
        $address5 = new Address('5_5', '5', '5', 3);
        //and
        //1-2-3
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:00', '2021-01-01 00:05', $client, $driver, $address1, $address2, $this->clock);
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:10', '2021-01-01 00:15', $client, $driver, $address2, $address3, $this->clock);
        //4-5
        $this->fixtures->aRequestedAndCompletedTransit('2021-01-01 00:20', '2021-01-01 00:25', $client, $driver, $address4, $address5, $this->clock);

        //when
        $response = $this->transitAnalyzerController->analyze($client->getId(), $address1->getId());

        //then
        //1-2-3
        self::assertSame(
            [$address1->getHash(), $address2->getHash(), $address3->getHash()],
            $this->getHashes($response->getContent())
        );
    }

    /**
     * @return int[]
     */
    private function getHashes(string $response): array
    {
        return array_map(fn(array $data) => $data['hash'], json_decode($response, true)['addresses']);
    }
}
