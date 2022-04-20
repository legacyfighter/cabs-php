<?php

namespace LegacyFighter\Cabs\Ride;

use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class TransitController
{
    public function __construct(private TransitService $transitService) {}

    #[Route('/transits/{requestUuid}', methods: ['GET'])]
    public function getTransit(Uuid $requestUuid): Response
    {
        return new JsonResponse($this->transitService->loadTransit($requestUuid));
    }

    #[Route('/transits', methods: ['POST'])]
    public function createTransit(TransitDTO $transitDTO): Response
    {
        return new JsonResponse($this->transitService->createTransit($transitDTO));
    }

    #[Route('/transits/{id}/changeAddressTo', methods: ['POST'])]
    public function changeAddressTo(int $id, AddressDTO $addressDTO): Response
    {
        $this->transitService->changeTransitAddressTo($this->transitService->getRequestUuid($id), $addressDTO);
        return new JsonResponse($this->transitService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/changeAddressFrom', methods: ['POST'])]
    public function changeAddressFrom(int $id, AddressDTO $addressDTO): Response
    {
        $this->transitService->changeTransitAddressFrom($this->transitService->getRequestUuid($id), $addressDTO);
        return new JsonResponse($this->transitService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/cancel', methods: ['POST'])]
    public function cancel(int $id): Response
    {
        $this->transitService->cancelTransit($this->transitService->getRequestUuid($id));
        return new JsonResponse($this->transitService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/publish', methods: ['POST'])]
    public function publishTransit(int $id): Response
    {
        $this->transitService->publishTransit($this->transitService->getRequestUuid($id));
        return new JsonResponse($this->transitService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/findDrivers', methods: ['POST'])]
    public function findDriversForTransit(int $id): Response
    {
        $this->transitService->findDriversForTransit($this->transitService->getRequestUuid($id));
        return new JsonResponse($this->transitService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/accept/{driverId}', methods: ['POST'])]
    public function acceptTransit(int $id, int $driverId): Response
    {
        $this->transitService->acceptTransit($driverId, $this->transitService->getRequestUuid($id));
        return new JsonResponse($this->transitService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/start/{driverId}', methods: ['POST'])]
    public function start(int $id, int $driverId): Response
    {
        $this->transitService->startTransit($driverId, $this->transitService->getRequestUuid($id));
        return new JsonResponse($this->transitService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/reject/{driverId}', methods: ['POST'])]
    public function reject(int $id, int $driverId): Response
    {
        $this->transitService->rejectTransit($driverId, $this->transitService->getRequestUuid($id));
        return new JsonResponse($this->transitService->loadTransitBy($id));
    }

    #[Route('/transits/{id}/complete/{driverId}', methods: ['POST'])]
    public function complete(int $id, int $driverId, AddressDTO $destination): Response
    {
        $this->transitService->completeTransit($driverId, $this->transitService->getRequestUuid($id), $destination);
        return new JsonResponse($this->transitService->loadTransitBy($id));
    }
}
