<?php

namespace LegacyFighter\Cabs\Repair\Legacy\User;

use LegacyFighter\Cabs\Repair\Legacy\Job\JobResult;
use LegacyFighter\Cabs\Repair\Legacy\Job\RepairJob;

class EmployeeDriverWithOwnCar extends EmployeeDriver
{
    private SignedContract $contract;

    protected function handleRepairJob(RepairJob $job): JobResult
    {
        $acceptedParts = array_intersect($job->getPartsToRepair(), $this->contract->getCoveredParts());

        $coveredCost = $job->getEstimatedValue()->percentage((int) $this->contract->getCoverageRatio());
        $totalCost = $job->getEstimatedValue()->subtract($coveredCost);

        return (new JobResult(JobResult::DECISION_ACCEPTED))->addParam('totalCost', $totalCost)->addParam('acceptedParts',$acceptedParts);
    }


    public function setContract(SignedContract $contract): void
    {
        $this->contract = $contract;
    }
}
