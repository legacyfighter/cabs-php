<?php

namespace LegacyFighter\Cabs\Geolocation\Address;

use Doctrine\ORM\EntityManagerInterface;

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

    public function findHashById(int $id): int
    {
        return (int) $this->em->getConnection()->executeQuery(
            'SELECT hash FROM address WHERE id = :id',
            ['id' => $id]
        )->fetchOne();
    }

    public function getByHash(int $hash): Address
    {
        return $this->em->getRepository(Address::class)->findOneBy(['hash' => $hash]);
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
