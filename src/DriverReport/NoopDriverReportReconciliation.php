<?php

namespace LegacyFighter\Cabs\DriverReport;

use LegacyFighter\Cabs\DTO\DriverReport;

class NoopDriverReportReconciliation implements DriverReportReconciliation
{
    public function compare(DriverReport $oldOne, DriverReport $newOne): void
    {

    }
}
