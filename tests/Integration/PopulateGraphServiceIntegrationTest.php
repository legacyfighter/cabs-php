<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use LegacyFighter\Cabs\TransitAnalyzer\GraphTransitAnalyzer;
use LegacyFighter\Cabs\TransitAnalyzer\PopulateGraphService;

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
        $driver = $this->fixtures->aDriver();
        //and
        $address1 = new Address('100_1', '1', '1', 1);
        $address2 = new Address('100_2', '2', '2', 2);
        $address3 = new Address('100_3', '3', '3', 3);
        $address4 = new Address('100_4', '4', '4', 3);
        //and
        //1-2-3-4
        $this->fixtures->aRequestedAndCompletedTransitByHand(10, 'now', 'now', $client, $driver, $address1, $address2);
        $this->fixtures->aRequestedAndCompletedTransitByHand(10, 'now', 'now', $client, $driver, $address2, $address3);
        $this->fixtures->aRequestedAndCompletedTransitByHand(10, 'now', 'now', $client, $driver, $address3, $address4);

        //when
        $this->populateGraphService->populate();

        //then
        self::assertSame(
            [$address1->getHash(), $address2->getHash(), $address3->getHash(), $address4->getHash()],
            $this->analyzer->analyze($client->getId(), $address1->getHash())
        );
    }
}
