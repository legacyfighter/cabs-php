<?php

namespace LegacyFighter\Cabs\Tests\Unit\DriverReport;

use LegacyFighter\Cabs\DriverReport\DriverReportCreator;
use LegacyFighter\Cabs\DriverReport\DriverReportReconciliation;
use LegacyFighter\Cabs\DriverReport\OldDriverReportCreator;
use LegacyFighter\Cabs\DriverReport\SqlBasedDriverReportCreator;
use LegacyFighter\Cabs\DTO\DriverReport;
use Pheature\Core\Toggle\Read\Feature as FeatureInterface;
use Pheature\Core\Toggle\Read\ToggleStrategies;
use Pheature\Model\Toggle\Feature;
use Pheature\Core\Toggle\Read\FeatureFinder;
use Pheature\Core\Toggle\Read\Toggle;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DriverReportCreatorTest extends TestCase
{
    private const DRIVER_ID = 1;
    private const LAST_DAYS = 3;

    private SqlBasedDriverReportCreator|MockObject $sqlBasedDriverReportCreator;
    private OldDriverReportCreator|MockObject $oldDriverReportCreator;
    private DriverReportReconciliation|MockObject $driverReportReconciliation;

    protected function setUp(): void
    {
        $this->sqlBasedDriverReportCreator = $this->createMock(SqlBasedDriverReportCreator::class);
        $this->oldDriverReportCreator = $this->createMock(OldDriverReportCreator::class);
        $this->driverReportReconciliation = $this->createMock(DriverReportReconciliation::class);
    }

    /**
     * @test
     */
    public function callsNewReport(): void
    {
        $creator = new DriverReportCreator(
            $this->sqlBasedDriverReportCreator,
            $this->oldDriverReportCreator,
            $this->driverReportReconciliation,
            $this->createToggle([
                'driver_report_sql' => true,
                'driver_report_creation_reconciliation' => false
            ])
        );

        $this->sqlBasedDriverReportCreator->expects($this->once())->method('createReport')->with(self::DRIVER_ID, self::LAST_DAYS);
        $this->oldDriverReportCreator->expects($this->never())->method('createReport');

        $creator->create(self::DRIVER_ID, self::LAST_DAYS);
    }

    /**
     * @test
     */
    public function callsOldReport(): void
    {
        $creator = new DriverReportCreator(
            $this->sqlBasedDriverReportCreator,
            $this->oldDriverReportCreator,
            $this->driverReportReconciliation,
            $this->createToggle([
                'driver_report_sql' => false,
                'driver_report_creation_reconciliation' => false
            ])
        );

        $this->oldDriverReportCreator->expects($this->once())->method('createReport')->with(self::DRIVER_ID, self::LAST_DAYS);
        $this->sqlBasedDriverReportCreator->expects($this->never())->method('createReport');

        $creator->create(self::DRIVER_ID, self::LAST_DAYS);
    }

    /**
     * @test
     */
    public function callsReconciliationAndUsesOldReport(): void
    {
        $creator = new DriverReportCreator(
            $this->sqlBasedDriverReportCreator,
            $this->oldDriverReportCreator,
            $this->driverReportReconciliation,
            $this->createToggle([
                'driver_report_sql' => false,
                'driver_report_creation_reconciliation' => true
            ])
        );
        $oldReport = new DriverReport();
        $sqlReport = new DriverReport();

        $this->oldDriverReportCreator
            ->expects($this->once())
            ->method('createReport')
            ->with(self::DRIVER_ID, self::LAST_DAYS)
            ->willReturn($oldReport)
        ;
        $this->sqlBasedDriverReportCreator
            ->expects($this->once())
            ->method('createReport')
            ->with(self::DRIVER_ID, self::LAST_DAYS)
            ->willReturn($sqlReport)
        ;
        $this->driverReportReconciliation
            ->expects($this->once())
            ->method('compare')
            ->with($oldReport, $sqlReport)
        ;

        $creator->create(self::DRIVER_ID, self::LAST_DAYS);
    }

    /**
     * @test
     */
    public function callsReconciliationAndUsesNewReport(): void
    {
        $creator = new DriverReportCreator(
            $this->sqlBasedDriverReportCreator,
            $this->oldDriverReportCreator,
            $this->driverReportReconciliation,
            $this->createToggle([
                'driver_report_sql' => true,
                'driver_report_creation_reconciliation' => true
            ])
        );
        $oldReport = new DriverReport();
        $sqlReport = new DriverReport();

        $this->oldDriverReportCreator
            ->expects($this->once())
            ->method('createReport')
            ->with(self::DRIVER_ID, self::LAST_DAYS)
            ->willReturn($oldReport)
        ;
        $this->sqlBasedDriverReportCreator
            ->expects($this->once())
            ->method('createReport')
            ->with(self::DRIVER_ID, self::LAST_DAYS)
            ->willReturn($sqlReport)
        ;
        $this->driverReportReconciliation
            ->expects($this->once())
            ->method('compare')
            ->with($oldReport, $sqlReport)
        ;

        $creator->create(self::DRIVER_ID, self::LAST_DAYS);
    }

    private function createToggle(array $config): Toggle
    {
        return new Toggle(new class($config) implements FeatureFinder {
            public function __construct(private array $config) {}

            public function get(string $featureId): FeatureInterface
            {
                return new Feature($featureId, new ToggleStrategies(), $this->config[$featureId] ?? false);
            }

            public function all(): array
            {
                return [];
            }
        });
    }
}
