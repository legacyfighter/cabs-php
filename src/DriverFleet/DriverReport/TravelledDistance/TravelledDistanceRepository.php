<?php

namespace LegacyFighter\Cabs\DriverFleet\DriverReport\TravelledDistance;

use Doctrine\ORM\EntityManagerInterface;

class TravelledDistanceRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(TravelledDistance $travelledDistance): TravelledDistance
    {
        $this->em->persist($travelledDistance);
        $this->em->flush();
        return $travelledDistance;
    }

    public function findTravelledDistanceTimeSlotByTime(\DateTimeImmutable $when, int $driverId): ?TravelledDistance
    {
        return $this->em->createQuery(sprintf(
            'SELECT td FROM %s td WHERE td.timeSlot.beginning <= :when AND :when < td.timeSlot.end AND td.driverId = :driverId',
            TravelledDistance::class
        ))->setParameters([
            'when' => $when,
            'driverId' => $driverId
        ])->getOneOrNullResult();
    }

    public function findTravelledDistanceByTimeSlotAndDriverId(TimeSlot $timeSlot, int $driverId): ?TravelledDistance
    {
        return $this->em->createQuery(sprintf(
            'SELECT td FROM %s td WHERE td.timeSlot.beginning = :beginning AND td.timeSlot.end = :end AND td.driverId = :driverId',
            TravelledDistance::class
        ))->setParameters([
            'beginning' => $timeSlot->beginning(),
            'end' => $timeSlot->end(),
            'driverId' => $driverId
        ])->getOneOrNullResult();
    }

    public function calculateDistance(\DateTimeImmutable $beginning, \DateTimeImmutable $to, int $driverId): float
    {
        return (float) $this->em->getConnection()->executeQuery(
            'SELECT COALESCE(SUM(_inner.distance), 0) FROM 
                ( (SELECT * FROM travelled_distance td WHERE td.beginning >= :beginning AND td.driver_id = :driverId)) 
            AS _inner WHERE "end" <= :to',
            [
                'beginning' => $beginning->format('Y-m-d H:i:s'),
                'to' => $to->format('Y-m-d H:i:s'),
                'driverId' => $driverId
            ]
        )->fetchOne();
    }
}
