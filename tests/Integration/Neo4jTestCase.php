<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class Neo4jTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        $this->getContainer()->get(ClientInterface::class)->transaction(function(TransactionInterface $tsx) {
            $tsx->run('MATCH (n) DETACH DELETE n');
        });
    }
}
