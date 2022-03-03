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
            ->andWhere($expr->gte('seenAt', $from))
            ->andWhere($expr->lte('seenAt', $to))
            ->orderBy(['seenAt' => Criteria::ASC])
        ;
        return $this->em->getRepository(DriverPosition::class)->matching($criteria)->toArray();
    }

    /**
     * @return DriverPositionDTOV2[]
     */
    public function findAverageDriverPositionSince(float $latitudeMin, float $latitudeMax, float $longitudeMin, float $longitudeMax, \DateTimeImmutable $date): array
    {
        return \array_map(
            static fn (array $data): DriverPositionDTOV2 => new DriverPositionDTOV2($data['driver'], $data['lat'], $data['lon'], new \DateTimeImmutable($data['seen'])),
            $this->em->createQueryBuilder()
                ->select(\sprintf('d as driver, avg(p.latitude) as lat, avg(p.longitude) as lon, max(p.seenAt) as seen'))
                ->from(Driver::class, 'd')
                ->join(DriverPosition::class, 'p', 'WITH', 'p.driver = d')
                ->where('p.latitude between :latitudeMin and :latitudeMax')
                ->andWhere('p.longitude between :longitudeMin and :longitudeMax')
                ->andWhere('p.seenAt >= :seenAt')
                ->setParameters([
                    'latitudeMin' => $latitudeMin,
                    'latitudeMax' => $latitudeMax,
                    'longitudeMin' => $longitudeMin,
                    'longitudeMax' => $longitudeMax,
                    'seenAt' => $date
                ])
                ->groupBy('d.id')
                ->getQuery()
                ->getResult());
    }
}
