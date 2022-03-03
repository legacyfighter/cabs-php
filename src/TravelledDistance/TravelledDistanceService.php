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

    public function addPosition(DriverPosition $driverPosition): void
    {
        $driverId = $driverPosition->getDriver()->getId();
        $matchedSlot = $this->travelledDistanceRepository->findTravelledDistanceTimeSlotByTime($driverPosition->getSeenAt(), $driverId);
        $now = $this->clock->now();
        if($matchedSlot !== null) {
            if($matchedSlot->containts($now)) {
                $this->addDistanceToSlot($driverPosition, $matchedSlot);
            } else if ($matchedSlot->isBefore($now)) {
                $this->recalculateDistanceFor($matchedSlot, $driverId);
            }
        } else {
            $currentTimeSlot = TimeSlot::slotThatContains($now);
            $prev = $currentTimeSlot->prev();
            $prevTravelledDistance = $this->travelledDistanceRepository->findTravelledDistanceByTimeSlotAndDriverId($prev, $driverId);
            if($prevTravelledDistance !== null) {
                if($prevTravelledDistance->endsAt($driverPosition->getSeenAt())) {
                    $this->addDistanceToSlot($driverPosition, $prevTravelledDistance);
                }
            }
            $this->createSlotForNow($driverPosition, $driverId, $currentTimeSlot);
        }
    }

    private function addDistanceToSlot(DriverPosition $driverPosition, TravelledDistance $aggregateDistance): void
    {
        $aggregateDistance->addDistance(
            Distance::ofKm($this->distanceCalculator->calculateByGeo(
                $driverPosition->getLatitude(),
                $driverPosition->getLongitude(),
                $aggregateDistance->getLastLatitude(),
                $aggregateDistance->getLastLongitude()
            )),
            $driverPosition->getLatitude(),
            $driverPosition->getLongitude()
        );
        $this->travelledDistanceRepository->save($aggregateDistance);
    }

    private function recalculateDistanceFor(TravelledDistance $aggregateDistance, int $driverId): void
    {
        // todo
    }

    private function createSlotForNow(DriverPosition $driverPosition, int $driverId, TimeSlot $timeSlot): void
    {
        $this->travelledDistanceRepository->save(new TravelledDistance($driverId, $timeSlot, $driverPosition));
    }
}
