<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverSession;

class DriverSessionRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(DriverSession $driverSession): DriverSession
    {
        $this->em->persist($driverSession);
        $this->em->flush();
        return $driverSession;
    }

    public function getOne(int $sessionId): ?DriverSession
    {
        return $this->em->find(DriverSession::class, $sessionId);
    }

    public function findTopByDriverAndLoggedOutAtIsNullOrderByLoggedAtDesc(Driver $driver): ?DriverSession
    {
        $expr = Criteria::expr();

        return $this->em->getRepository(DriverSession::class)->matching(
            Criteria::create()
                ->where($expr->eq('driver', $driver))
                ->andWhere($expr->isNull('loggedOutAt'))
                ->orderBy(['loggedAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->toArray()[0] ?? null;
    }

    /**
     * @param Driver[] $drivers
     * @param string[] $carClasses
     * @return DriverSession[]
     */
    public function findAllByLoggedOutAtNullAndDriverInAndCarClassIn(array $drivers, array $carClasses): array
    {
        $expr = Criteria::expr();

        return $this->em->getRepository(DriverSession::class)->matching(
            Criteria::create()
                ->where($expr->in('driver', $drivers))
                ->andWhere($expr->in('carClass', $carClasses))
                ->andWhere($expr->isNull('loggedOutAt'))
        )->toArray();
    }

    /**
     * @return DriverSession[]
     */
    public function findAllByDriverAndLoggedAtAfter(Driver $driver, \DateTimeImmutable $since): array
    {
        $expr = Criteria::expr();

        return $this->em->getRepository(DriverSession::class)->matching(
            Criteria::create()
                ->where($expr->eq('driver', $driver))
                ->andWhere($expr->gt('loggedAt', $since))
        )->toArray();
    }

    /**
     * @return DriverSession[]
     */
    public function findByDriver(Driver $driver): array
    {
        return $this->em->getRepository(DriverSession::class)->findBy(['driver' => $driver]);
    }
}
