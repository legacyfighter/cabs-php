<?php

namespace LegacyFighter\Cabs\Tracking;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\DriverFleet\Driver;

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
     * @return DriverPositionDTOV2[]
     */
    public function findAverageDriverPositionSince(float $latitudeMin, float $latitudeMax, float $longitudeMin, float $longitudeMax, \DateTimeImmutable $date): array
    {
        return \array_map(
            static fn (array $data): DriverPositionDTOV2 => new DriverPositionDTOV2($data['driver'], $data['lat'], $data['lon'], new \DateTimeImmutable($data['seen'])),
            $this->em->createQueryBuilder()
                ->select(\sprintf('d.id as driver, avg(p.latitude) as lat, avg(p.longitude) as lon, max(p.seenAt) as seen'))
                ->from(Driver::class, 'd')
                ->join(DriverPosition::class, 'p', 'WITH', 'p.driverId = d.id')
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
