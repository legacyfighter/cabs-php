<?php

namespace LegacyFighter\Cabs\Repair\Legacy\User;

use LegacyFighter\Cabs\Repair\Legacy\Job\JobResult;
use LegacyFighter\Cabs\Repair\Legacy\Job\RepairJob;

class EmployeeDriverWithLeasedCar extends EmployeeDriver
{
    private int $lasingCompanyId;

    protected function handleRepairJob(RepairJob $job): JobResult
    {
        return (new JobResult(JobResult::DECISION_REDIRECTION))->addParam('shouldHandleBy', $this->lasingCompanyId);
    }

    public function getLasingCompanyId(): int
    {
        return $this->lasingCompanyId;
    }

    public function setLasingCompanyId(int $lasingCompanyId): void
    {
        $this->lasingCompanyId = $lasingCompanyId;
    }
}
