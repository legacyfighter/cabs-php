<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Crm\TransitAnalyzer\GraphTransitAnalyzer;
use LegacyFighter\Cabs\Crm\TransitAnalyzer\PopulateGraphService;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Tests\Common\Fixtures;

class PopulateGraphServiceIntegrationTest extends Neo4jTestCase
{
    private Fixtures $fixtures;
    private GraphTransitAnalyzer $analyzer;
    private PopulateGraphService $populateGraphService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
        $this->analyzer = $this->getContainer()->get(GraphTransitAnalyzer::class);
        $this->populateGraphService = $this->getContainer()->get(PopulateGraphService::class);
    }

    /**
     * @test
     */
    public function canPopulateGraphWithDataFromRelationalDB(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $address1 = new Address('100_1', '1', '1', 1);
        $address2 = new Address('100_2', '2', '2', 2);
        $address3 = new Address('100_3', '3', '3', 3);
        $address4 = new Address('100_4', '4', '4', 3);
        //and
        //1-2-3-4
        $this->aTransitFromTo($address1, $address2, $client);
        $this->aTransitFromTo($address2, $address3, $client);
        $this->aTransitFromTo($address3, $address4, $client);

        //when
        $this->populateGraphService->populate();

        //then
        self::assertSame(
            [$address1->getHash(), $address2->getHash(), $address3->getHash(), $address4->getHash()],
            $this->analyzer->analyze($client->getId(), $address1->getHash())
        );
    }

    private function aTransitFromTo(Address $pickup, Address $destination, Client $client): void
    {
        $this->fixtures->aJourney(10, $client, $this->fixtures->aNearbyDriver(), $pickup, $destination);
    }
}
