<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\TransitDetails\TransitDetails;

class TransitRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getOne(int $transitId): ?Transit
    {
        return $this->em->find(Transit::class, $transitId);
    }

    public function save(Transit $transit): Transit
    {
        $this->em->persist($transit);
        $this->em->flush();
        return $transit;
    }

    /**
     * @return Transit[]
     */
    public function findByClient(Client $client): array
    {
        return $this->em->getRepository(Transit::class)->findBy(['client' => $client]);
    }

    /**
     * @return \Generator<Transit>|Transit[]
     */
    public function findAllByStatus(string $status): \Generator
    {
        $query = $this->em->createQuery(sprintf('SELECT t FROM %s t WHERE t.status = :status', Transit::class))->setParameter('status', $status);
        $batchSize = 100;
        $i = 1;

        foreach ($query->toIterable() as $transit) {
            yield $transit;
            $i++;
            if(($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }
    }

    /**
     * @return Transit[]
     */
    public function findAllByDriverAndDateTimeBetween(Driver $driver, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->em->createQuery(sprintf('SELECT t FROM %s t JOIN %s td WITH t.id = td.transitId WHERE t.driver = :driver AND td.dateTime >= :from AND td.dateTime <= :to' , Transit::class, TransitDetails::class))
            ->setParameters([
                'driver' => $driver,
                'from' => $from,
                'to' => $to
            ])
            ->getResult();
    }
}
