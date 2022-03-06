<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use LegacyFighter\Cabs\TransitAnalyzer\GraphTransitAnalyzer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GraphTransitAnalyzerIntegrationTest extends KernelTestCase
{
    private GraphTransitAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = $this->getContainer()->get(GraphTransitAnalyzer::class);
        $this->getContainer()->get(ClientInterface::class)->transaction(function(TransactionInterface $tsx) {
            $tsx->run('MATCH (n) DETACH DELETE n');
        });
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
