<?php

namespace LegacyFighter\Cabs\TransitDetails;

use Doctrine\ORM\EntityManagerInterface;

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
}
