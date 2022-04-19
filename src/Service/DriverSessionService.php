<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\CarFleet\CarTypeService;
use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\DriverFleet\DriverRepository;
use LegacyFighter\Cabs\Entity\DriverSession;
use LegacyFighter\Cabs\Repository\DriverSessionRepository;

class DriverSessionService
{
    public function __construct(
        private DriverSessionRepository $driverSessionRepository,
        private CarTypeService $carTypeService,
        private Clock $clock
    )
    {
    }

    public function logIn(int $driverId, string $plateNumber, string $carClass, string $carBrand): DriverSession
    {
        $session = new DriverSession();
        $session->setDriverId($driverId);
        $session->setLoggedAt($this->clock->now());
        $session->setCarClass($carClass);
        $session->setPlatesNumber($plateNumber);
        $session->setCarBrand($carBrand);
        $this->carTypeService->registerActiveCar($session->getCarClass());
        return $this->driverSessionRepository->save($session);
    }

    public function logOut(int $sessionId): void
    {
        $session = $this->driverSessionRepository->getOne($sessionId);
        if($session === null) {
            throw new \InvalidArgumentException('Session does not exist');
        }
        $this->carTypeService->unregisterCar($session->getCarClass());
        $session->setLoggedOutAt($this->clock->now());
    }

    public function logOutCurrentSession(int $driverId): void
    {
        $session = $this->driverSessionRepository->findTopByDriverAndLoggedOutAtIsNullOrderByLoggedAtDesc($driverId);
        if($session !== null) {
            $session->setLoggedOutAt($this->clock->now());
            $this->carTypeService->unregisterCar($session->getCarClass());
        }
    }

    /**
     * @return DriverSession[]
     */
    public function findByDriver(int $driverId): array
    {
        return $this->driverSessionRepository->findByDriverId($driverId);
    }
}
