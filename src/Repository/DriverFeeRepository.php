<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverFee;

class DriverFeeRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByDriverId(int $driverId): ?DriverFee
    {
        return $this->em->getRepository(DriverFee::class)->findOneBy(['driver' => $driverId]);
    }

    public function save(DriverFee $driverFee): DriverFee
    {
        $this->em->persist($driverFee);
        $this->em->flush();
        return $driverFee;
    }
}
