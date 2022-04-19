<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Crm\TransitAnalyzer\GraphTransitAnalyzer;

class GraphTransitAnalyzerIntegrationTest extends Neo4jTestCase
{
    private GraphTransitAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = $this->getContainer()->get(GraphTransitAnalyzer::class);
    }

    /**
     * @test
     */
    public function canRecognizeNewAddress(): void
    {
        //given
        $this->analyzer->addTransitBetweenAddresses(1, 1, 111, 222, new \DateTimeImmutable(), new \DateTimeImmutable());
        $this->analyzer->addTransitBetweenAddresses(1, 1, 222, 333  , new \DateTimeImmutable(), new \DateTimeImmutable());
        $this->analyzer->addTransitBetweenAddresses(1, 1, 333, 444, new \DateTimeImmutable(), new \DateTimeImmutable());

        //when
        $result = $this->analyzer->analyze(1, 111);

        //then
        self::assertSame([111, 222, 333, 444], $result);
    }
}
