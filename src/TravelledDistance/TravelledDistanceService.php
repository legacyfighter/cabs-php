<?php

namespace LegacyFighter\Cabs\TravelledDistance;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\Entity\DriverPosition;
use LegacyFighter\Cabs\Entity\TimeSlot;
use LegacyFighter\Cabs\Entity\TravelledDistance;
use LegacyFighter\Cabs\Service\DistanceCalculator;

class TravelledDistanceService
{
    public function __construct(
        private Clock $clock,
        private TravelledDistanceRepository $travelledDistanceRepository,
        private DistanceCalculator $distanceCalculator
    ) {}

    public function calculateDistance(int $driverId, \DateTimeImmutable $from, \DateTimeImmutable $to): Distance
    {
        return Distance::ofKm($this->travelledDistanceRepository->calculateDistance(
            TimeSlot::slotThatContains($from)->beginning(),
            TimeSlot::slotThatContains($to)->end(),
            $driverId
        ));
    }

    public function addPosition(int $driverId, float $latitude, float $longitude, \DateTimeImmutable $seenAt): void
    {
        $matchedSlot = $this->travelledDistanceRepository->findTravelledDistanceTimeSlotByTime($seenAt, $driverId);
        $now = $this->clock->now();
        if($matchedSlot !== null) {
            if($matchedSlot->containts($now)) {
                $this->addDistanceToSlot($matchedSlot, $latitude, $longitude);
            } else if ($matchedSlot->isBefore($now)) {
                $this->recalculateDistanceFor($matchedSlot, $driverId);
            }
        } else {
            $currentTimeSlot = TimeSlot::slotThatContains($now);
            $prev = $currentTimeSlot->prev();
            $prevTravelledDistance = $this->travelledDistanceRepository->findTravelledDistanceByTimeSlotAndDriverId($prev, $driverId);
            if($prevTravelledDistance !== null) {
                if($prevTravelledDistance->endsAt($seenAt)) {
                    $this->addDistanceToSlot($prevTravelledDistance, $latitude, $longitude);
                }
            }
            $this->createSlotForNow($driverId, $currentTimeSlot, $latitude, $longitude);
        }
    }

    private function addDistanceToSlot(TravelledDistance $aggregateDistance, float $latitude, float $longitude): void
    {
        $aggregateDistance->addDistance(
            Distance::ofKm($this->distanceCalculator->calculateByGeo(
                $latitude,
                $longitude,
                $aggregateDistance->getLastLatitude(),
                $aggregateDistance->getLastLongitude()
            )),
            $latitude,
            $longitude
        );
        $this->travelledDistanceRepository->save($aggregateDistance);
    }

    private function recalculateDistanceFor(TravelledDistance $aggregateDistance, int $driverId): void
    {
        // todo
    }

    private function createSlotForNow(int $driverId, TimeSlot $timeSlot, float $latitude, float $longitude): void
    {
        $this->travelledDistanceRepository->save(new TravelledDistance($driverId, $timeSlot, $latitude, $longitude));
    }
}
