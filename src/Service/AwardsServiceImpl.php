<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Config\AppProperties;
use LegacyFighter\Cabs\DTO\AwardsAccountDTO;
use LegacyFighter\Cabs\Entity\AwardsAccount;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Miles\AwardedMiles;
use LegacyFighter\Cabs\Entity\Miles\ConstantUntil;
use LegacyFighter\Cabs\Repository\AwardedMilesRepository;
use LegacyFighter\Cabs\Repository\AwardsAccountRepository;
use LegacyFighter\Cabs\Repository\ClientRepository;
use LegacyFighter\Cabs\Repository\TransitRepository;

class AwardsServiceImpl implements AwardsService
{
    private AwardsAccountRepository $accountRepository;
    private AwardedMilesRepository $milesRepository;
    private ClientRepository $clientRepository;
    private TransitRepository $transitRepository;
    private Clock $clock;
    private AppProperties $appProperties;

    public function __construct(
        AwardsAccountRepository $accountRepository,
        AwardedMilesRepository $milesRepository,
        ClientRepository $clientRepository,
        TransitRepository $transitRepository,
        Clock $clock,
        AppProperties $appProperties)
    {
        $this->accountRepository = $accountRepository;
        $this->milesRepository = $milesRepository;
        $this->clientRepository = $clientRepository;
        $this->transitRepository = $transitRepository;
        $this->clock = $clock;
        $this->appProperties = $appProperties;
    }

    public function findBy(int $clientId): AwardsAccountDTO
    {
        return AwardsAccountDTO::from($this->accountRepository->findByClient($this->clientRepository->getOne($clientId)));
    }

    public function registerToProgram(int $clientId): void
    {
        $client = $this->clientRepository->getOne($clientId);

        if($client === null) {
            throw new \InvalidArgumentException('Client does not exists, id = '.$clientId);
        }

        $account = new AwardsAccount();
        $account->setClient($client);
        $account->setActive(false);
        $account->setDate($this->clock->now());

        $this->accountRepository->save($account);
    }

    public function activateAccount(int $clientId): void
    {
        $account = $this->accountRepository->findByClient($this->clientRepository->getOne($clientId));

        if($account === null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$clientId);
        }

        $account->setActive(true);

        $this->accountRepository->save($account);
    }

    public function deactivateAccount(int $clientId): void
    {
        $account = $this->accountRepository->findByClient($this->clientRepository->getOne($clientId));

        if($account === null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$clientId);
        }

        $account->setActive(false);

        $this->accountRepository->save($account);
    }

    public function registerMiles(int $clientId, int $transitId): ?AwardedMiles
    {
        $account = $this->accountRepository->findByClient($this->clientRepository->getOne($clientId));
        $transit = $this->transitRepository->getOne($transitId);
        if($transit === null) {
            throw new \InvalidArgumentException('Transit does not exists, id = '.$transitId);
        }

        $now = $this->clock->now();
        if($account === null || !$account->isActive()) {
            return null;
        } else {
            $miles = new AwardedMiles();
            $miles->setTransit($transit);
            $miles->setDate($this->clock->now());
            $miles->setClient($account->getClient());
            $miles->setMiles(ConstantUntil::until($this->appProperties->getDefaultMilesBonus(), $now->modify(sprintf('+%s days', $this->appProperties->getMilesExpirationInDays()))));
            $account->increaseTransactions();

            $this->milesRepository->save($miles);
            $this->accountRepository->save($account);

            return $miles;
        }
    }

    public function registerNonExpiringMiles(int $clientId, int $miles): AwardedMiles
    {
        $account = $this->accountRepository->findByClient($this->clientRepository->getOne($clientId));

        if($account === null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$clientId);
        } else {
            $_miles = new AwardedMiles();
            $_miles->setTransit(null);
            $_miles->setClient($account->getClient());
            $_miles->setMiles(ConstantUntil::untilForever($miles));
            $_miles->setDate($this->clock->now());
            $account->increaseTransactions();
            $this->milesRepository->save($_miles);
            $this->accountRepository->save($account);
            return $_miles;
        }
    }

    public function removeMiles(int $clientId, int $miles): void
    {
        $client = $this->clientRepository->getOne($clientId);
        $account = $this->accountRepository->findByClient($client);

        if($account===null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$clientId);
        } else {
            if($this->calculateBalance($clientId) >= $miles && $account->isActive()) {
                $milesList = $this->milesRepository->findAllByClient($client);
                $transitsCounter = count($this->transitRepository->findByClient($client));
                if(count($client->getClaims()) >= 3) {
                    usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getExpirationDate() <=> $b->getExpirationDate());
                    $milesList = array_reverse($milesList);
                    usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getExpirationDate() === null ? -1 : ($b->getExpirationDate() === null ? 1 : 0));
                } else if($client->getType() === Client::TYPE_VIP) {
                    usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getExpirationDate() <=> $b->getExpirationDate());
                    usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => (int) $a->cantExpire() <=> (int) $b->cantExpire());
                } else if($transitsCounter >= 15 && $this->isSunday()) {
                    usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getExpirationDate() <=> $b->getExpirationDate());
                    usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => (int) $a->cantExpire() <=> (int) $b->cantExpire());
                } else if($transitsCounter >= 15) {
                    usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getDate() <=> $b->getDate());
                    usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => (int) $a->cantExpire() <=> (int) $b->cantExpire());
                } else {
                    usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getDate() <=> $b->getDate());
                }

                $now = $this->clock->now();
                foreach ($milesList as $iter) {
                    if($miles <= 0) {
                        break;
                    }
                    if($iter->cantExpire() || $iter->getExpirationDate() > $this->clock->now()) {
                        $milesAmount = $iter->getMilesAmount($now);
                        if($milesAmount <= $miles) {
                            $miles -= $milesAmount;
                            $iter->removeAll($now);
                        } else {
                            $iter->subtract($miles, $now);
                            $miles = 0;
                        }
                        $this->milesRepository->save($iter);
                    }
                }
            } else {
                throw new \InvalidArgumentException('Insufficient miles, id = '.$clientId.', miles requested = '.$miles);
            }
        }
    }

    public function calculateBalance(int $clientId): int
    {
        $client = $this->clientRepository->getOne($clientId);
        $milesList = $this->milesRepository->findAllByClient($client);

        $now = $this->clock->now();
        return array_sum(
            array_map(
                fn(AwardedMiles $miles) => $miles->getMilesAmount($now),
                array_filter(
                    $milesList,
                    fn(AwardedMiles $miles) => $miles->getExpirationDate() !== null && $miles->getExpirationDate() > $this->clock->now() || $miles->cantExpire())
            )
        );
    }

    public function transferMiles(int $fromClientId, int $toClientId, int $miles): void
    {
        $fromClient = $this->clientRepository->getOne($fromClientId);
        $accountFrom = $this->accountRepository->findByClient($fromClient);
        $accountTo = $this->accountRepository->findByClient($this->clientRepository->getOne($toClientId));
        if($accountFrom === null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$fromClientId);
        }
        if($accountTo === null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$toClientId);
        }
        if($this->calculateBalance($fromClientId) >= $miles && $accountFrom->isActive()) {
            $milesList = $this->milesRepository->findAllByClient($fromClient);
            $now = $this->clock->now();

            foreach ($milesList as $iter) {
                if($iter->cantExpire() || $iter->getExpirationDate() > $this->clock->now()) {
                    $milesAmount = $iter->getMilesAmount($now);
                    if($milesAmount <= $miles) {
                        $iter->setClient($accountTo->getClient());
                        $miles -= $milesAmount;
                    } else {
                        $iter->subtract($miles, $now);
                        $_miles = new AwardedMiles();

                        $_miles->setClient($accountTo->getClient());
                        $_miles->setMiles($iter->getMiles());

                        $miles -= $milesAmount;

                        $this->milesRepository->save($_miles);
                    }
                    $this->milesRepository->save($iter);
                }
            }

            $accountFrom->increaseTransactions();
            $accountTo->increaseTransactions();

            $this->accountRepository->save($accountFrom);
            $this->accountRepository->save($accountTo);
        }
    }

    private function isSunday(): bool
    {
        return $this->clock->now()->format('l') === 'Sunday';
    }
}
