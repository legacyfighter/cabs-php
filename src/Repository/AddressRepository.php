<?php

namespace LegacyFighter\Cabs\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Entity\Address;

class AddressRepository
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getOne(int $addressId): ?Address
    {
        return $this->em->find(Address::class, $addressId);
    }

    // FIX ME: To replace with getOrCreate method instead of that?
    // Actual workaround for address uniqueness problem: assign result from repo.save to variable for later usage
    public function save(Address $address): Address
    {
        $address->hash();
        if(!$this->isIdSet($address)) {
            $existingAddress = $this->em->getRepository(Address::class)->findOneBy(['hash' => $address->getHash()]);
            if($existingAddress !== null) {
                return $existingAddress;
            }
        }
        $this->em->persist($address);
        $this->em->flush();
        return $address;
    }

    private function isIdSet(Address $address): bool
    {
        try {
            $address->getId();
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
