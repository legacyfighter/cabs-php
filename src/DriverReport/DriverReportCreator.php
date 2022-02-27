<?php

namespace LegacyFighter\Cabs\DriverReport;

use LegacyFighter\Cabs\DTO\DriverReport;
use Pheature\Core\Toggle\Read\Toggle;

class DriverReportCreator
{
    public function __construct(
        private SqlBasedDriverReportCreator $sqlBasedDriverReportCreator,
        private OldDriverReportCreator $oldDriverReportCreator,
        private DriverReportReconciliation $driverReportReconciliation,
        private Toggle $toggle
    )
    {
    }

    public function create(int $driverId, int $days): DriverReport
    {
        $newReport = null;
        $oldReport = null;
        if($this->toggle->isEnabled('driver_report_creation_reconciliation')) {
            $newReport = $this->sqlBasedDriverReportCreator->createReport($driverId, $days);
            $oldReport = $this->oldDriverReportCreator->createReport($driverId, $days);
            $this->driverReportReconciliation->compare($oldReport, $newReport);
        }

        if($this->toggle->isEnabled('driver_report_sql')) {
            if($newReport===null) {
                $newReport = $this->sqlBasedDriverReportCreator->createReport($driverId, $days);
            }
            return $newReport;
        }

        if($oldReport === null) {
            $oldReport = $this->oldDriverReportCreator->createReport($driverId, $days);
        }

        return $oldReport;
    }
}
