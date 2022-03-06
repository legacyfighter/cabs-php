<?php

namespace LegacyFighter\Cabs\TransitAnalyzer;

use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Databags\SummarizedResult;
use LegacyFighter\Cabs\Entity\Events\TransitCompleted;

class GraphTransitAnalyzer
{
    public function __construct(
        private ClientInterface $client
    ) {}

    /**
     * @return int[]
     */
    public function analyze(int $clientId, int $addressHash): array
    {
        return $this->client->readTransaction(function (TransactionInterface $tsx) use ($clientId, $addressHash) {
            /** @var SummarizedResult $result */
            $result = $tsx->run('MATCH p=(a:Address)-[:Transit*]->(:Address)
                WHERE a.hash = $addressHash
                AND (ALL(x IN range(1, length(p)-1) WHERE ((relationships(p)[x]).clientId = $clientId) AND 0 <= duration.inSeconds( (relationships(p)[x-1]).completeAt, (relationships(p)[x]).started).minutes <= 15))
                AND length(p) >= 1
                RETURN [x in nodes(p) | x.hash] AS hashes ORDER BY length(p) DESC LIMIT 1
            ', ['addressHash' => $addressHash, 'clientId' => $clientId]);

            return $result->first()->getAsCypherList('hashes')->toArray();
        });
    }

    public function addTransitBetweenAddresses(
        int $clientId,
        int $transitId,
        int $addressFromHash,
        int $addressToHash,
        \DateTimeImmutable $started,
        \DateTimeImmutable $completeAt
    ): void
    {
        $this->client->writeTransaction(function (TransactionInterface $tsx) use ($clientId, $transitId, $addressFromHash, $addressToHash, $started, $completeAt) {
            $tsx->run('MERGE (from:Address {hash: $addressFromHash})', ['addressFromHash' => $addressFromHash]);
            $tsx->run('MERGE (to:Address {hash: $addressToHash})', ['addressToHash' => $addressToHash]);
            $tsx->run('MATCH (from:Address {hash: $addressFromHash}), (to:Address {hash: $addressToHash})
                CREATE (from)-[:Transit {clientId: $clientId, transitId: $transitId,
                started: datetime($started), completeAt: datetime($completeAt) }]->(to)',
            [
                'addressFromHash' => $addressFromHash,
                'addressToHash' => $addressToHash,
                'clientId' => $clientId,
                'transitId' => $transitId,
                'started' => $started->format(DATE_ISO8601),
                'completeAt' => $completeAt->format(DATE_ISO8601)
            ]);
        });
    }

    public function handle(TransitCompleted $event): void
    {
        $this->addTransitBetweenAddresses(
            $event->clientId(),
            $event->transitId(),
            $event->addressFromHash(),
            $event->addressToHash(),
            $event->started(),
            $event->completeAt()
        );
    }
}
