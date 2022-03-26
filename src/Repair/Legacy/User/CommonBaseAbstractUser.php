<?php

namespace LegacyFighter\Cabs\Repair\Legacy\User;

use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Repair\Legacy\Job\CommonBaseAbstractJob;
use LegacyFighter\Cabs\Repair\Legacy\Job\JobResult;
use LegacyFighter\Cabs\Repair\Legacy\Job\MaintenanceJob;
use LegacyFighter\Cabs\Repair\Legacy\Job\RepairJob;

class CommonBaseAbstractUser extends BaseEntity
{
    public function doJob(CommonBaseAbstractJob $job): JobResult
    {
        //poor man's pattern matching
        if($job instanceof RepairJob) {
            return $this->handleRepairJob($job);
        }
        if($job instanceof MaintenanceJob) {
            return $this->handleMaintenanceJob($job);
        }

        return $this->defaultHandler($job);
    }

    protected function handleRepairJob(RepairJob $job): JobResult
    {
        return $this->defaultHandler($job);
    }

    protected function handleMaintenanceJob(MaintenanceJob $job): JobResult
    {
        return $this->defaultHandler($job);
    }

    protected function defaultHandler(CommonBaseAbstractJob $job): JobResult
    {
        throw new \InvalidArgumentException(sprintf('%s can not handle %s', static::class, get_class($job)));
    }
}
