<?php

namespace LegacyFighter\Cabs\DriverReport;

use LegacyFighter\Cabs\DTO\DriverReport;

interface DriverReportReconciliation
{
    public function compare(DriverReport $oldOne, DriverReport $newOne): void;
}
