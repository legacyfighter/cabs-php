<?php

namespace LegacyFighter\Cabs\DriverReport;

use LegacyFighter\Cabs\DTO\DriverReport;
use Pheature\Core\Toggle\Read\Toggle;

class DriverReportCreator
{
    public function __construct(
        private SqlBasedDriverReportCreator $sqlBasedDriverReportCreator,
        private OldDriverReportCreator $oldDriverReportCreator,
        private Toggle $toggle
    )
    {
    }

    public function create(int $driverId, int $days): DriverReport
    {
        if($this->toggle->isEnabled('driver_report_sql')) {
            return $this->sqlBasedDriverReportCreator->createReport($driverId, $days);
        }
        return $this->oldDriverReportCreator->createReport($driverId, $days);
    }
}
