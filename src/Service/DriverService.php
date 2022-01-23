<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\DTO\DriverDTO;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverAttribute;
use LegacyFighter\Cabs\Entity\DriverLicense;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repository\DriverAttributeRepository;
use LegacyFighter\Cabs\Repository\DriverFeeRepository;
use LegacyFighter\Cabs\Repository\DriverRepository;
use LegacyFighter\Cabs\Repository\TransitRepository;

class DriverService
{
    public const DRIVER_LICENSE_REGEX = '/^[A-Z9]{5}\d{6}[A-Z9]{2}\d[A-Z]{2}$/';

    private DriverRepository $driverRepository;
    private DriverAttributeRepository $driverAttributeRepository;
    private TransitRepository $transitRepository;
    private DriverFeeService $driverFeeService;

    public function __construct(DriverRepository $driverRepository, DriverAttributeRepository $driverAttributeRepository, TransitRepository $transitRepository, DriverFeeService $driverFeeService)
    {
        $this->driverRepository = $driverRepository;
        $this->driverAttributeRepository = $driverAttributeRepository;
        $this->transitRepository = $transitRepository;
        $this->driverFeeService = $driverFeeService;
    }

    public function createDriver(string $license, string $lastName, string $firstName, string $type, string $status, ?string $photo): Driver
    {
        $driver = new Driver();
        if($status === Driver::STATUS_ACTIVE) {
            $driver->setDriverLicense(DriverLicense::withLicense($license));
        } else {
            $driver->setDriverLicense(DriverLicense::withoutValidation($license));
        }
        $driver->setLastName($lastName);
        $driver->setFirstName($firstName);
        $driver->setType($type);
        $driver->setStatus($status);
        if($photo !== null && $photo !== '') {
            if(base64_decode($photo) !== false) {
                $driver->setPhoto($photo);
            } else {
                throw new \InvalidArgumentException('Illegal photo in base64');
            }
        }
        return $this->driverRepository->save($driver);
    }

    public function changeLicenseNumber(string $newLicense, int $driverId): void
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exists, id = '.$driverId);
        }
        $driver->setDriverLicense(DriverLicense::withLicense($newLicense));

        if($driver->getStatus() !== Driver::STATUS_ACTIVE) {
            throw new \InvalidArgumentException('Driver is not active, cannot change license');
        }
    }

    public function changeDriverStatus(int $driverId, string $status): void
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exists, id = '.$driverId);
        }
        if($status === Driver::STATUS_ACTIVE) {
            $driver->setDriverLicense(DriverLicense::withLicense($driver->getDriverLicense()->asString()));
        }

        $driver->setStatus($status);
    }

    public function changePhoto(int $driverId, string $photo): void
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exists, id = '.$driverId);
        }
        if($photo !== null && $photo !== '') {
            if(base64_decode($photo) !== false) {
                $driver->setPhoto($photo);
            } else {
                throw new \InvalidArgumentException('Illegal photo in base64');
            }
        }
        $driver->setPhoto($photo);
        $this->driverRepository->save($driver);
    }

    public function calculateDriverMonthlyPayment(int $driverId, int $year, int $month): Money
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exists, id = '.$driverId);
        }
        $from = new \DateTimeImmutable(sprintf('first day of %s-%s', $year, $month));
        $to = (new \DateTimeImmutable(sprintf('last day of %s-%s', $year, $month)))->modify('+1 day');

        $transitsList = $this->transitRepository->findAllByDriverAndDateTimeBetween($driver, $from, $to);
        $sum = array_reduce(
            array_map(
                fn(Transit $transit) => $this->driverFeeService->calculateDriverFee($transit->getId()),
                $transitsList
            ),
            fn(Money $sum, Money $fee) => $sum->add($fee),
            Money::zero()
        );

        return $sum;
    }

    /**
     * @return array<string,Money>
     */
    public function calculateDriverYearlyPayment(int $driverId, int $year): array
    {
        $payments = [];
        foreach (range(1, 12) as $month) {
            $payments[$month] = $this->calculateDriverMonthlyPayment($driverId, $year, $month);
        }
        return  $payments;
    }

    public function load(int $driverId): DriverDTO
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exists, id = '.$driverId);
        }
        return new DriverDTO($driver);
    }

    public function addAttribute(int $driverId, string $attributeName, string $value): void
    {
        $driver = $this->driverRepository->getOne($driverId);
        if($driver === null) {
            throw new \InvalidArgumentException('Driver does not exists, id = '.$driverId);
        }
        $this->driverAttributeRepository->save(new DriverAttribute($attributeName, $value, $driver));
    }
}
