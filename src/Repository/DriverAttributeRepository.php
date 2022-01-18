<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\DriverAttribute;

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
