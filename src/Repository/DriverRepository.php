<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Driver;

class DriverRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(Driver $driver): Driver
    {
        $this->em->persist($driver);
        $this->em->flush();
        return $driver;
    }

    public function getOne(int $driverId): ?Driver
    {
        return $this->em->find(Driver::class, $driverId);
    }
}
