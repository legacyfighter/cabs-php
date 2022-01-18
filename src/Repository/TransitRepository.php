<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\Transit;

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
     * @return Transit[]
     */
    public function findAllByDriverAndDateTimeBetween(Driver $driver, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $expr = Criteria::expr();
        return $this->em->getRepository(Transit::class)->matching(Criteria::create()
            ->where($expr->eq('driver', $driver))
            ->andWhere($expr->gt('dateTime', $from))
            ->andWhere($expr->lt('dateTime', $to))
        )->toArray();
    }

    /**
     * @return Transit[]
     */
    public function findAllByClientAndFromAndStatusOrderByDateTimeDesc(Client $client, Address $address, string $status): array
    {
        return $this->em->getRepository(Transit::class)->findBy([
            'client' => $client,
            'from' => $address,
            'status' => $status
        ], [
            'dateTime' => Criteria::DESC
        ]);
    }

    /**
     * @return Transit[]
     */
    public function findAllByClientAndFromAndPublishedAfterAndStatusOrderByDateTimeDesc(Client $client, Address $address, \DateTimeImmutable $when, string $status): array
    {
        $expr = Criteria::expr();
        return $this->em->getRepository(Transit::class)->matching(Criteria::create()
            ->where($expr->eq('client', $client))
            ->andWhere($expr->eq('from', $address))
            ->andWhere($expr->gt('published', $when))
            ->andWhere($expr->eq('status', $status))
            ->orderBy(['dateTime' => Criteria::DESC])
        )->toArray();
    }
}
