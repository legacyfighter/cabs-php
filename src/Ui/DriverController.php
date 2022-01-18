<?php

namespace LegacyFighter\Cabs\Ui;

use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Repository\DriverRepository;
use LegacyFighter\Cabs\Service\DriverService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DriverController
{
    public function __construct(
        private DriverService $driverService,
        private DriverRepository $driverRepository
    ) {}

    #[Route('/drivers', methods: ['POST'], )]
    public function createDriver(Request $request): Response
    {
        $data = $request->toArray();
        $driver = $this->driverService->createDriver($data['license'], $data['lastName'], $data['firstName'], Driver::TYPE_CANDIDATE, Driver::STATUS_INACTIVE, $data['photo'] ?? null);
        return new JsonResponse($this->driverService->load($driver->getId()));
    }

    #[Route('/drivers/{id}', methods: ['GET'], )]
    public function getDriver(int $id): Response {return new JsonResponse($this->driverService->load($id));}

    #[Route('/drivers/{id}', methods: ['POST'], )]
    public function updateDriver(int $id): Response
    {

        return new JsonResponse($this->driverService->load($id));
    }

    #[Route('/drivers/{id}/deactivate', methods: ['POST'], )]
    public function deactivateDriver(int $id): Response
    {
        $this->driverService->changeDriverStatus($id, Driver::STATUS_INACTIVE);
        return new JsonResponse($this->driverService->load($id));
    }

    #[Route('/drivers/{id}/activate', methods: ['POST'], )]
    public function activateDriver(int $id): Response
    {
        $this->driverService->changeDriverStatus($id, Driver::STATUS_ACTIVE);
        return new JsonResponse($this->driverService->load($id));
    }
}
