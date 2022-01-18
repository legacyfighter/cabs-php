<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\DTO\DriverPositionDTOV2;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverPosition;

class DriverPositionRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(DriverPosition $driverPosition): DriverPosition
    {
        $this->em->persist($driverPosition);
        $this->em->flush();
        return $driverPosition;
    }

    /**
     * @return DriverPosition[]
     */
    public function findByDriverAndSeenAtBetweenOrderBySeenAtAsc(Driver $driver, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create()
            ->where($expr->eq('driver', $driver))
            ->andWhere($expr->gt('seenAt', $from))
            ->andWhere($expr->lt('seenAt', $to))
            ->orderBy(['seenAt' => Criteria::ASC])
        ;
        return $this->em->getRepository(DriverPosition::class)->matching($criteria)->toArray();
    }

    /**
     * @return DriverPositionDTOV2[]
     */
    public function findAverageDriverPositionSince(float $latitudeMin, float $latitudeMax, float $longitudeMin, float $longitudeMax, \DateTimeImmutable $date): array
    {
        return $this->em->createQueryBuilder()
            ->select('NEW LegacyFighter\Cabs\DTO\DriverPositionDTOV2(p.driver, avg(p.latitude), avg(p.longitude), max(p.seenAt))')
            ->from(DriverPosition::class, 'p')
            ->where('p.latitude between :latitudeMin and :latitudeMax')
            ->andWhere('p.longitude between :longitudeMin and :longitudeMax')
            ->andWhere('p.seenAt >= :seenAt')
            ->groupBy('p.driver.id')
            ->setParameters([
                'latitudeMin' => $latitudeMin,
                'latitudeMax' => $latitudeMax,
                'longitudeMin' => $longitudeMin,
                'longitudeMax' => $longitudeMax,
                'seenAt' => $date
            ])
            ->getQuery()
            ->getResult();
    }
}
