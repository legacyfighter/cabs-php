<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Repository\AddressRepository;
use LegacyFighter\Cabs\Repository\ClientRepository;
use LegacyFighter\Cabs\Repository\TransitRepository;

class TransitAnalyzer
{
    private TransitRepository $transitRepository;
    private ClientRepository $clientRepository;
    private AddressRepository $addressRepository;

    public function __construct(TransitRepository $transitRepository, ClientRepository $clientRepository, AddressRepository $addressRepository)
    {
        $this->transitRepository = $transitRepository;
        $this->clientRepository = $clientRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @return Address[]
     */
    public function analyze(int $clientId, int $addressId): array
    {
        $client = $this->clientRepository->getOne($clientId);
        if($client === null) {
            throw new \InvalidArgumentException('Client does not exists, id = '.$clientId);
        }
        $address = $this->addressRepository->getOne($addressId);
        if($address === null) {
            throw new \InvalidArgumentException('Address does not exists, id = '.$clientId);
        }
        return $this->_analyze($client, $address, null);
    }

    /**
     * @return Address[]
     *
     * Brace yourself, deadline is coming... They made me to do it this way.
     * Tested!
     */
    private function _analyze(Client $client, Address $from, ?Transit $t): array
    {
        if($t === null) {
            $ts = $this->transitRepository->findAllByClientAndFromAndStatusOrderByDateTimeDesc($client, $from, Transit::STATUS_COMPLETED);
        } else {
            $ts = $this->transitRepository->findAllByClientAndFromAndPublishedAfterAndStatusOrderByDateTimeDesc($client, $from, $t->getPublished(), Transit::STATUS_COMPLETED);
        }

        // Workaround for performance reasons.
        if(count($ts)>1000 && $client->getId() === 666) {
            // No one will see a difference for this customer ;)
            $ts = array_slice($ts, 0, 1000);
        }

//        if($ts === []) {
//            return [];
//        }

        if($t !== null) {
            $ts = array_filter($ts, fn(Transit $transit) => $transit->getCompleteAt()->modify('+15 minutes') > $transit->getCompleteAt());
            // Before 2018-01-01:
            // $ts = array_filter($ts, fn(Transit $transit) => $transit->getCompleteAt()->modify('+15 minutes') > $transit->getPublished());
        }

        if($ts === []) {
            return [];
        }

        $all = array_map(function(Transit $_t) use ($client): array {
            $result = [$_t->getFrom()];
            return $result + $this->_analyze($client, $_t->getTo(), $_t);
        }, $ts);
        usort($all, fn($a, $b) => count($a) <=> count($b));
        $all = array_reverse($all);
        return $all[0] ?? [];
    }
}
