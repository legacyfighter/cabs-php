<?php

namespace LegacyFighter\Cabs\Repair\Legacy\DAO;

use LegacyFighter\Cabs\Repair\Legacy\Parts\Parts;
use LegacyFighter\Cabs\Repair\Legacy\User\CommonBaseAbstractUser;
use LegacyFighter\Cabs\Repair\Legacy\User\EmployeeDriverWithOwnCar;
use LegacyFighter\Cabs\Repair\Legacy\User\SignedContract;

/**
 * Fake impl that fakes graph query and determining CommonBaseAbstractUser type
 */
class UserDAO
{
    public function getOne(int $userId): CommonBaseAbstractUser
    {
        $contract = new SignedContract();
        $contract->setCoveredParts([Parts::ENGINE, Parts::GEARBOX, Parts::PAINT, Parts::SUSPENSION]);
        $contract->setCoverageRatio(100);

        $user = new EmployeeDriverWithOwnCar();
        $user->setContract($contract);

        return $user;
    }
}
