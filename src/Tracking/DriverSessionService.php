<?php

namespace LegacyFighter\Cabs\Tracking;

use LegacyFighter\Cabs\CarFleet\CarTypeService;
use LegacyFighter\Cabs\Common\Clock;

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

    /**
     * @param int[] $driversId
     * @param string[] $carClasses
     * @return int[]
     */
    public function findCurrentlyLoggedDriverIds(array $driversId, array $carClasses): array
    {
        return array_map(
            fn(DriverSession $ds) => $ds->getDriverId(),
            $this->driverSessionRepository->findAllByLoggedOutAtNullAndDriverInAndCarClassIn($driversId, $carClasses)
        );
    }
}
