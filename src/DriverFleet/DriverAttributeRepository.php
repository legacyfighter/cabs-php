<?php

namespace LegacyFighter\Cabs\DriverFleet;

use Doctrine\ORM\EntityManagerInterface;

class DriverAttributeRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(DriverAttribute $driverAttribute): DriverAttribute
    {
        $this->em->persist($driverAttribute);
        $this->em->flush();
        return $driverAttribute;
    }
}
