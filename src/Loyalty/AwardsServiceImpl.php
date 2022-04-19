<?php

namespace LegacyFighter\Cabs\Loyalty;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Config\AppProperties;
use LegacyFighter\Cabs\Crm\Claims\ClaimService;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\Crm\ClientService;
use LegacyFighter\Cabs\Repository\TransitRepository;

class AwardsServiceImpl implements AwardsService
{

    private ClaimService $claimService;

    public function __construct(
        private AwardsAccountRepository $accountRepository,
        private TransitRepository $transitRepository,
        private Clock $clock,
        private AppProperties $appProperties,
        private ClientService $clientService,
    )
    { }

    public function setClaimService(ClaimService $claimService): void
    {
        $this->claimService = $claimService;
    }

    public function findBy(int $clientId): AwardsAccountDTO
    {
        return AwardsAccountDTO::from(
            $this->accountRepository->findByClientId($clientId),
            $this->clientService->load($clientId)
        );
    }

    public function registerToProgram(int $clientId): void
    {
        $client = $this->clientService->load($clientId);
        if($client === null) {
            throw new \InvalidArgumentException('Client does not exists, id = '.$clientId);
        }

        $this->accountRepository->save(AwardsAccount::notActiveAccount($clientId, $this->clock->now()));
    }

    public function activateAccount(int $clientId): void
    {
        $account = $this->accountRepository->findByClientId($clientId);
        if($account === null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$clientId);
        }

        $account->activate();
        $this->accountRepository->save($account);
    }

    public function deactivateAccount(int $clientId): void
    {
        $account = $this->accountRepository->findByClientId($clientId);
        if($account === null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$clientId);
        }

        $account->deactivate();
        $this->accountRepository->save($account);
    }

    public function registerMiles(int $clientId, int $transitId): ?AwardedMiles
    {
        $account = $this->accountRepository->findByClientId($clientId);

        $now = $this->clock->now();
        if($account === null || !$account->isActive()) {
            return null;
        } else {
            $expireAt = $now->modify(sprintf('+%s days', $this->appProperties->getMilesExpirationInDays()));
            $miles = $account->addExpiringMiles($this->appProperties->getDefaultMilesBonus(), $expireAt, $transitId, $now);
            $this->accountRepository->save($account);
            return $miles;
        }
    }

    public function registerNonExpiringMiles(int $clientId, int $miles): AwardedMiles
    {
        $account = $this->accountRepository->findByClientId($clientId);

        if($account === null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$clientId);
        } else {
            $_miles = $account->addNonExpiringMiles($miles, $this->clock->now());
            $this->accountRepository->save($account);
            return $_miles;
        }
    }

    public function removeMiles(int $clientId, int $miles): void
    {
        $client = $this->clientService->load($clientId);
        $account = $this->accountRepository->findByClientId($clientId);

        if($account===null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$clientId);
        }

        $account->remove(
            $miles,
            $this->clock->now(),
            $this->chooseStrategy(
                count($this->transitRepository->findByClientId($clientId)),
                $this->claimService->getNumberOfClaims($clientId),
                $client->getType(),
                $this->isSunday()
            )
        );
    }

    public function calculateBalance(int $clientId): int
    {
        $account = $this->accountRepository->findByClientId($clientId);
        return $account->calculateBalance($this->clock->now());
    }

    public function transferMiles(int $fromClientId, int $toClientId, int $miles): void
    {
        $accountFrom = $this->accountRepository->findByClientId($fromClientId);
        $accountTo = $this->accountRepository->findByClientId($toClientId);
        if($accountFrom === null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$fromClientId);
        }
        if($accountTo === null) {
            throw new \InvalidArgumentException('Account does not exists, id = '.$toClientId);
        }
        $accountFrom->moveMilesTo($accountTo, $miles, $this->clock->now());
        $this->accountRepository->save($accountFrom);
        $this->accountRepository->save($accountTo);
    }

    private function isSunday(): bool
    {
        return $this->clock->now()->format('l') === 'Sunday';
    }

    private function chooseStrategy(int $transitsCounter, int $claimsCounter, string $clientType, bool $isSunday): callable
    {
        if($claimsCounter >= 3) {
            return function(array &$milesList) {
                usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getExpirationDate() <=> $b->getExpirationDate());
                $milesList = array_reverse($milesList);
                usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getExpirationDate() === null ? -1 : ($b->getExpirationDate() === null ? 1 : 0));
            };
        } else if($clientType === Client::TYPE_VIP) {
            return function(array &$milesList) {
                usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getExpirationDate() <=> $b->getExpirationDate());
                usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => (int)$a->cantExpire() <=> (int)$b->cantExpire());
            };
        } else if($transitsCounter >= 15 && $isSunday) {
            return function(array &$milesList) {
                usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getExpirationDate() <=> $b->getExpirationDate());
                usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => (int)$a->cantExpire() <=> (int)$b->cantExpire());
            };
        } else if($transitsCounter >= 15) {
            return function(array &$milesList) {
                usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getDate() <=> $b->getDate());
                usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => (int)$a->cantExpire() <=> (int)$b->cantExpire());
            };
        } else {
            return function(array &$milesList) {
                usort($milesList, fn(AwardedMiles $a, AwardedMiles $b) => $a->getDate() <=> $b->getDate());
            };
        }
    }
}
