<?php

namespace LegacyFighter\Cabs\Contracts\Legacy;

use Doctrine\ORM\EntityManagerInterface;

class UserRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(User $user): User
    {
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function getOne(int $userId): ?User
    {
        return $this->em->find(User::class, $userId);
    }
}
