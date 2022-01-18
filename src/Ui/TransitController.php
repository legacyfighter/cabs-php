<?php

namespace LegacyFighter\Cabs\Ui;

use LegacyFighter\Cabs\DTO\AddressDTO;
use LegacyFighter\Cabs\DTO\TransitDTO;
use LegacyFighter\Cabs\Service\TransitService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransitController
{
    public function __construct(private TransitService $transitService) {}

    #[Route('/transits/{id}', methods: ['GET'])]
    public function getTransit(int $id): Response
    {
        return new JsonResponse($this->transitService->loadTransit($id));
    }

    #[Route('/transits', methods: ['POST'])]
    public function createTransit(TransitDTO $transitDTO): Response
    {
        $transit = $this->transitService->createTransit($transitDTO);
        return new JsonResponse($this->transitService->loadTransit($transit->getId()));
    }

    #[Route('/transits/{id}/changeAddressTo', methods: ['POST'])]
    public function changeAddressTo(int $id, AddressDTO $addressDTO): Response
    {
        $this->transitService->changeTransitAddressTo($id, $addressDTO);
        return new JsonResponse($this->transitService->loadTransit($id));
    }

    #[Route('/transits/{id}/changeAddressFrom', methods: ['POST'])]
    public function changeAddressFrom(int $id, AddressDTO $addressDTO): Response
    {
        $this->transitService->changeTransitAddressFrom($id, $addressDTO);
        return new JsonResponse($this->transitService->loadTransit($id));
    }

    #[Route('/transits/{id}/cancel', methods: ['POST'])]
    public function cancel(int $id): Response
    {
        $this->transitService->cancelTransit($id);
        return new JsonResponse($this->transitService->loadTransit($id));
    }

    #[Route('/transits/{id}/publish', methods: ['POST'])]
    public function publishTransit(int $id): Response
    {
        $this->transitService->publishTransit($id);
        return new JsonResponse($this->transitService->loadTransit($id));
    }

    #[Route('/transits/{id}/findDrivers', methods: ['POST'])]
    public function findDriversForTransit(int $id): Response
    {
        $this->transitService->findDriversForTransit($id);
        return new JsonResponse($this->transitService->loadTransit($id));
    }

    #[Route('/transits/{id}/accept/{driverId}', methods: ['POST'])]
    public function acceptTransit(int $id, int $driverId): Response
    {
        $this->transitService->acceptTransit($driverId, $id);
        return new JsonResponse($this->transitService->loadTransit($id));
    }

    #[Route('/transits/{id}/start/{driverId}', methods: ['POST'])]
    public function start(int $id, int $driverId): Response
    {
        $this->transitService->startTransit($driverId, $id);
        return new JsonResponse($this->transitService->loadTransit($id));
    }

    #[Route('/transits/{id}/reject/{driverId}', methods: ['POST'])]
    public function reject(int $id, int $driverId): Response
    {
        $this->transitService->rejectTransit($driverId, $id);
        return new JsonResponse($this->transitService->loadTransit($id));
    }

    #[Route('/transits/{id}/complete/{driverId}', methods: ['POST'])]
    public function complete(int $id, int $driverId, AddressDTO $destination): Response
    {
        $this->transitService->completeTransit($driverId, $id, $destination);
        return new JsonResponse($this->transitService->loadTransit($id));
    }
}
