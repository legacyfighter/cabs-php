<?php

namespace LegacyFighter\Cabs\CarFleet;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarTypeController
{
    public function __construct(private CarTypeService $carTypeService) {}

    #[Route('/cartypes', methods: ['POST'])]
    public function create(CarTypeDTO $carTypeDTO): Response
    {
        return new JsonResponse($this->carTypeService->create($carTypeDTO));
    }

    #[Route('/cartypes/{carClass}/registerCar', methods: ['POST'])]
    public function registerCar(string $carClass): Response
    {
        $this->carTypeService->registerCar($carClass);
        return new JsonResponse();
    }

    #[Route('/cartypes/{carClass}/unregisterCar', methods: ['POST'])]
    public function unregisterCar(string $carClass): Response
    {
        $this->carTypeService->unregisterCar($carClass);
        return new JsonResponse();
    }

    #[Route('/cartypes/{id}/activate', methods: ['POST'])]
    public function activate(int $id): Response
    {
        $this->carTypeService->activate($id);
        return new JsonResponse();
    }

    #[Route('/cartypes/{id}/deactivate', methods: ['POST'])]
    public function deactivate(int $id): Response
    {
        $this->carTypeService->deactivate($id);
        return new JsonResponse();
    }

    #[Route('/cartypes/{id}', methods: ['GET'])]
    public function find(int $id): Response
    {
        return new JsonResponse($this->carTypeService->loadDto($id));
    }
}
