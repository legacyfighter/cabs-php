<?php

namespace LegacyFighter\Cabs\Tracking;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;

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

    public function findTopByDriverAndLoggedOutAtIsNullOrderByLoggedAtDesc(int $driverId): ?DriverSession
    {
        $expr = Criteria::expr();

        return $this->em->getRepository(DriverSession::class)->matching(
            Criteria::create()
                ->where($expr->eq('driverId', $driverId))
                ->andWhere($expr->isNull('loggedOutAt'))
                ->orderBy(['loggedAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->toArray()[0] ?? null;
    }

    /**
     * @param int[] $driverIds
     * @param string[] $carClasses
     * @return DriverSession[]
     */
    public function findAllByLoggedOutAtNullAndDriverInAndCarClassIn(array $driverIds, array $carClasses): array
    {
        $expr = Criteria::expr();

        return $this->em->getRepository(DriverSession::class)->matching(
            Criteria::create()
                ->where($expr->in('driverId', $driverIds))
                ->andWhere($expr->in('carClass', $carClasses))
                ->andWhere($expr->isNull('loggedOutAt'))
        )->toArray();
    }

    /**
     * @return DriverSession[]
     */
    public function findByDriverId(int $driverId): array
    {
        return $this->em->getRepository(DriverSession::class)->findBy(['driverId' => $driverId]);
    }
}
