<?php

namespace LegacyFighter\Cabs\Repair\Legacy\Service;

use LegacyFighter\Cabs\Repair\Legacy\DAO\UserDAO;
use LegacyFighter\Cabs\Repair\Legacy\Job\CommonBaseAbstractJob;
use LegacyFighter\Cabs\Repair\Legacy\Job\JobResult;

class JobDoer
{
    private UserDAO $userDAO;

    public function __construct(UserDAO $userDAO)
    {
        $this->userDAO = $userDAO;
    }

    public function repair(int $userId, CommonBaseAbstractJob $job): JobResult
    {
        $user = $this->userDAO->getOne($userId);
        return $user->doJob($job);
    }

    public function repair2parallelModels(int $userId, CommonBaseAbstractJob $job): JobResult
    {
        $user = $this->userDAO->getOne($userId);
        return $user->doJob($job);
    }
}
