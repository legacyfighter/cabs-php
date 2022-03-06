<?php

namespace LegacyFighter\Cabs\Config;

use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;

class Neo4jClientFactory
{
    public static function create(string $neo4jDsn): ClientInterface
    {
        return ClientBuilder::create()
            ->withDriver('bolt', $neo4jDsn)
            ->withDefaultDriver('bolt')
            ->build();
    }
}
