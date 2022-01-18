<?php

namespace LegacyFighter\Cabs\Ui;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\DTO\DriverSessionDTO;
use LegacyFighter\Cabs\Entity\DriverSession;
use LegacyFighter\Cabs\Service\DriverSessionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DriverSessionController
{
    public function __construct(
        private DriverSessionService $driverSessionService,
        Clock $clock
    ) {}

    #[Route('/drivers/{driverId}/driverSessions/login', methods: ['POST'])]
    public function logIn(int $driverId, DriverSessionDTO $driverSessionDTO): Response
    {
        $this->driverSessionService->logIn($driverId, $driverSessionDTO->getPlatesNumber(), $driverSessionDTO->getCarClass(), $driverSessionDTO->getCarBrand());
        return new JsonResponse();
    }

    #[Route('/drivers/{driverId}/driverSessions/{sessionId}', methods: ['DELETE'])]
    public function logOut(int $driverId, int $sessionId): Response
    {
        $this->driverSessionService->logOut($sessionId);
        return new JsonResponse();
    }

    #[Route('/drivers/{driverId}/driverSessions', methods: ['DELETE'])]
    public function logOutCurrent(int $driverId): Response
    {
        $this->driverSessionService->logOutCurrentSession($driverId);
        return new JsonResponse();
    }

    #[Route('/drivers/{driverId}/driverSessions', methods: ['GET'])]
    public function list(int $driverId): Response
    {
        return new JsonResponse(array_map(
            fn(DriverSession $s) => DriverSessionDTO::from($s),
            $this->driverSessionService->findByDriver($driverId)
        ));
    }
}
