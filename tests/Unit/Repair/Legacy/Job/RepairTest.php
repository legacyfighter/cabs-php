<?php

namespace LegacyFighter\Cabs\Tests\Unit\Repair\Legacy\Job;

use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repair\Legacy\Job\JobResult;
use LegacyFighter\Cabs\Repair\Legacy\Job\RepairJob;
use LegacyFighter\Cabs\Repair\Legacy\Parts\Parts;
use LegacyFighter\Cabs\Repair\Legacy\User\EmployeeDriverWithOwnCar;
use LegacyFighter\Cabs\Repair\Legacy\User\SignedContract;
use PHPUnit\Framework\TestCase;

class RepairTest extends TestCase
{
    /**
     * @test
     */
    public function employeeDriverWithOwnCarCoveredByWarrantyShouldRepairForFree(): void
    {
        //given
        $employee = new EmployeeDriverWithOwnCar();
        $employee->setContract($this->fullCoverageWarranty());
        //when
        $result = $employee->doJob($this->fullRepair());
        //then
        self::assertEquals(JobResult::DECISION_ACCEPTED, $result->getDecision());
        self::assertEquals(Money::zero(), $result->getParam('totalCost'));
        self::assertEquals($this->allParts(), $result->getParam('acceptedParts'));
    }

    private function fullRepair(): RepairJob
    {
        $job = new RepairJob();
        $job->setEstimatedValue(Money::from(50000));
        $job->setPartsToRepair($this->allParts());

        return $job;
    }

    private function fullCoverageWarranty(): SignedContract
    {
        $contract = new SignedContract();
        $contract->setCoverageRatio(100);
        $contract->setCoveredParts($this->allParts());

        return $contract;
    }

    /**
     * @return string[]
     */
    private function allParts(): array
    {
        return [Parts::SUSPENSION, Parts::PAINT, Parts::GEARBOX, Parts::ENGINE];
    }
}
