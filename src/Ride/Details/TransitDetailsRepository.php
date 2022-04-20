<?php

namespace LegacyFighter\Cabs\Ride\Details;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class TransitDetailsRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(TransitDetails $transitDetails): TransitDetails
    {
        $this->em->persist($transitDetails);
        $this->em->flush();
        return $transitDetails;
    }

    public function findByTransitId(int $transitId): TransitDetails
    {
        $transitDetails = $this->em->getRepository(TransitDetails::class)->findOneBy(['transitId' => $transitId]);
        if(!$transitDetails instanceof TransitDetails) {
            throw new \RuntimeException('Transit details not found');
        }

        return $transitDetails;
    }

    public function findByRequestUuid(Uuid $requestUuid): TransitDetails
    {
        $transitDetails = $this->em->getRepository(TransitDetails::class)->findOneBy(['requestUuid' => $requestUuid]);
        if(!$transitDetails instanceof TransitDetails) {
            throw new \RuntimeException('Transit details not found');
        }

        return $transitDetails;
    }

    /**
     * @return TransitDetails[]
     */
    public function findByClientId(int $clientId): array
    {
        return $this->em->getRepository(TransitDetails::class)->findBy(['client' => $clientId]);
    }

    /**
     * @return TransitDetails[]
     */
    public function findByStatus(string $status): array
    {
        return $this->em->getRepository(TransitDetails::class)->findBy(['status' => $status]);
    }

    /**
     * @return TransitDetails[]
     */
    public function findAllByDriverAndDateTimeBetween(int $driverId, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->em->createQuery(sprintf('SELECT td FROM %s td WHERE td.driverId = :driverId AND td.dateTime >= :from AND td.dateTime <= :to' , TransitDetails::class))
            ->setParameters([
                'driverId' => $driverId,
                'from' => $from,
                'to' => $to
            ])
            ->getResult();
    }
}
