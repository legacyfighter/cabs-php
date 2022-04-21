<?php

namespace LegacyFighter\Cabs\Assignment;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class DriverAssignmentRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(DriverAssignment $driverAssignment): DriverAssignment
    {
        $this->em->persist($driverAssignment);
        $this->em->flush();
        return $driverAssignment;
    }

    public function findByRequestUuid(Uuid $requestUuid): ?DriverAssignment
    {
        return $this->em->getRepository(DriverAssignment::class)->findOneBy([
            'requestId' => $requestUuid
        ]);
    }

    public function findByRequestUuidAndStatus(Uuid $requestId, string $status): ?DriverAssignment
    {
        return $this->em->getRepository(DriverAssignment::class)->findOneBy([
            'requestId' => $requestId,
            'status' => $status
        ]);
    }
}
