<?php

namespace LegacyFighter\Cabs\Tests\Unit\Repair\Legacy\Service;

use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Repair\Legacy\DAO\UserDAO;
use LegacyFighter\Cabs\Repair\Legacy\Job\JobResult;
use LegacyFighter\Cabs\Repair\Legacy\Job\RepairJob;
use LegacyFighter\Cabs\Repair\Legacy\Parts\Parts;
use LegacyFighter\Cabs\Repair\Legacy\Service\JobDoer;
use PHPUnit\Framework\TestCase;

class JobDoerTest extends TestCase
{
    /**
     * @test
     */
    public function employeeWithOwnCarWithWarrantyShouldHaveCoveredAllPartsForFree(): void
    {
        $jobDoer = new JobDoer(new UserDAO());

        $result = $jobDoer->repair(1, $this->repairJob());

        self::assertEquals(JobResult::DECISION_ACCEPTED, $result->getDecision());
        self::assertEquals(Money::zero(), $result->getParam('totalCost'));
        self::assertEquals($this->allParts(), $result->getParam('acceptedParts'));
    }

    private function repairJob(): RepairJob
    {
        $job = new RepairJob();
        $job->setEstimatedValue(Money::from(7000));
        $job->setPartsToRepair($this->allParts());

        return $job;
    }

    /**
     * @return string[]
     */
    private function allParts(): array
    {
        return [Parts::ENGINE, Parts::GEARBOX, Parts::PAINT, Parts::SUSPENSION];
    }
}
