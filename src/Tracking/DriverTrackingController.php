<?php

namespace LegacyFighter\Cabs\Tracking;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DriverTrackingController
{
    public function __construct(private DriverTrackingService $trackingService) {}

    #[Route('/driverPositions', methods: ['POST'])]
    public function create(DriverPositionDTO $driverPositionDTO): Response
    {
        $driverPosition = $this->trackingService->registerPosition($driverPositionDTO->getDriverId(), $driverPositionDTO->getLatitude(), $driverPositionDTO->getLongitude(), $driverPositionDTO->getSeenAt());
        return new JsonResponse($this->toDto($driverPosition));
    }

    #[Route('/driverPositions/{id}/total', methods: ['GET'])]
    public function calculateTravelledDistance(int $id, Request $request): Response
    {
        return new JsonResponse($this->trackingService->calculateTravelledDistance($id, new \DateTimeImmutable($request->get('from')), new \DateTimeImmutable($request->get('to')))->toKmInFloat());
    }

    private function toDto(DriverPosition $driverPosition): DriverPositionDTO
    {
        return DriverPositionDTO::from(
            $driverPosition->getDriverId(),
            $driverPosition->getLatitude(),
            $driverPosition->getLongitude(),
            $driverPosition->getSeenAt()
        );
    }
}
