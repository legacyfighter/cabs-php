<?php

namespace LegacyFighter\Cabs\Ride;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\Ride\Details\TransitDetails;
use Symfony\Component\Uid\Uuid;

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
    public function findByClientId(int $clientId): array
    {
        return $this->em->createQuery(sprintf('SELECT t FROM %s t JOIN %s td WITH t.id = td.transitId WHERE td.client = :client' , Transit::class, TransitDetails::class))
            ->setParameters([
                'client' => $clientId,
            ])
            ->getResult();
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
        return $this->em->createQuery(sprintf('SELECT t FROM %s t JOIN %s td WITH t.id = td.transitId WHERE t.driverId = :driverId AND td.dateTime >= :from AND td.dateTime <= :to' , Transit::class, TransitDetails::class))
            ->setParameters([
                'driverId' => $driver->getId(),
                'from' => $from,
                'to' => $to
            ])
            ->getResult();
    }

    public function findByTransitRequestUuid(Uuid $requestUuid): ?Transit
    {
        return $this->em->getRepository(Transit::class)->findOneBy(['requestUuid' => $requestUuid]);
    }
}
