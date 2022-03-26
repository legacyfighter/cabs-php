<?php

namespace LegacyFighter\Cabs\Party\Infra;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Party\Model\Party\Party;
use LegacyFighter\Cabs\Party\Model\Party\PartyRepository;
use Symfony\Component\Uid\Uuid;

class DoctrinePartyRepository implements PartyRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function put(Uuid $id): Party
    {
        $party = $this->em->find(Party::class, $id);
        if($party === null) {
            $party = new Party($id);
            $this->em->persist($party);
            $this->em->flush();
        }

        return $party;
    }

}
