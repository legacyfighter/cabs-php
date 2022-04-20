<?php

namespace LegacyFighter\Cabs\Ride;

use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class TransitController
{
    public function __construct(private RideService $rideService) {}

    #[Route('/transits/{requestUuid}', methods: ['GET'])]
    public function getTransit(Uuid $requestUuid): Response
    {
        return new JsonResponse($this->rideService->loadTransit($requestUuid));
    }

    #[Route('/transits', methods: ['POST'])]
    public function createTransit(TransitDTO $transitDTO): Response
    {
        return new JsonResponse($this->rideService->createTransit($transitDTO));
    }

    #[Route('/transits/{id}/changeAddressTo', methods: ['POST'])]
    public function changeAddressTo(int $id, AddressDTO $addressDTO): Response
    {
        $this->rideService->changeTransitAddressTo($this->rideService->getRequestUuid($id), $addressDTO);
        return new JsonResponse($this->rideService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/changeAddressFrom', methods: ['POST'])]
    public function changeAddressFrom(int $id, AddressDTO $addressDTO): Response
    {
        $this->rideService->changeTransitAddressFrom($this->rideService->getRequestUuid($id), $addressDTO);
        return new JsonResponse($this->rideService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/cancel', methods: ['POST'])]
    public function cancel(int $id): Response
    {
        $this->rideService->cancelTransit($this->rideService->getRequestUuid($id));
        return new JsonResponse($this->rideService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/publish', methods: ['POST'])]
    public function publishTransit(int $id): Response
    {
        $this->rideService->publishTransit($this->rideService->getRequestUuid($id));
        return new JsonResponse($this->rideService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/findDrivers', methods: ['POST'])]
    public function findDriversForTransit(int $id): Response
    {
        $this->rideService->findDriversForTransit($this->rideService->getRequestUuid($id));
        return new JsonResponse($this->rideService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/accept/{driverId}', methods: ['POST'])]
    public function acceptTransit(int $id, int $driverId): Response
    {
        $this->rideService->acceptTransit($driverId, $this->rideService->getRequestUuid($id));
        return new JsonResponse($this->rideService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/start/{driverId}', methods: ['POST'])]
    public function start(int $id, int $driverId): Response
    {
        $this->rideService->startTransit($driverId, $this->rideService->getRequestUuid($id));
        return new JsonResponse($this->rideService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/reject/{driverId}', methods: ['POST'])]
    public function reject(int $id, int $driverId): Response
    {
        $this->rideService->rejectTransit($driverId, $this->rideService->getRequestUuid($id));
        return new JsonResponse($this->rideService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/complete/{driverId}', methods: ['POST'])]
    public function complete(int $id, int $driverId, AddressDTO $destination): Response
    {
        $this->rideService->completeTransit($driverId, $this->rideService->getRequestUuid($id), $destination);
        return new JsonResponse($this->rideService->loadTransitBy($id));
    }
}
