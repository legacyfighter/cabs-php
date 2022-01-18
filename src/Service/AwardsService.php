<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\DTO\AwardsAccountDTO;
use LegacyFighter\Cabs\Entity\AwardedMiles;

interface AwardsService
{
    public function findBy(int $clientId): AwardsAccountDTO;

    public function registerToProgram(int $clientId): void;

    public function activateAccount(int $clientId): void;

    public function deactivateAccount(int $clientId): void;

    public function registerMiles(int $clientId, int $transitId): ?AwardedMiles;

    public function registerSpecialMiles(int $clientId, int $miles): AwardedMiles;

    public function removeMiles(int $clientId, int $miles): void;

    public function calculateBalance(int $clientId): int;

    public function transferMiles(int $fromClientId, int $toClientId, int $miles): void;
}
