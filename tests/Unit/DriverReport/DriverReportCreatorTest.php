<?php

namespace LegacyFighter\Cabs\Tests\Unit\DriverReport;

use LegacyFighter\Cabs\DriverReport\DriverReportCreator;
use LegacyFighter\Cabs\DriverReport\OldDriverReportCreator;
use LegacyFighter\Cabs\DriverReport\SqlBasedDriverReportCreator;
use Pheature\Core\Toggle\Read\ChainToggleStrategyFactory;
use Pheature\Core\Toggle\Read\Toggle;
use Pheature\InMemory\Toggle\InMemoryConfig;
use Pheature\InMemory\Toggle\InMemoryFeatureFactory;
use Pheature\InMemory\Toggle\InMemoryFeatureFinder;
use Pheature\Model\Toggle\SegmentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DriverReportCreatorTest extends TestCase
{
    private const DRIVER_ID = 1;
    private const LAST_DAYS = 3;

    private SqlBasedDriverReportCreator|MockObject $sqlBasedDriverReportCreator;
    private OldDriverReportCreator|MockObject $oldDriverReportCreator;

    protected function setUp(): void
    {
        $this->sqlBasedDriverReportCreator = $this->createMock(SqlBasedDriverReportCreator::class);
        $this->oldDriverReportCreator = $this->createMock(OldDriverReportCreator::class);
    }

    /**
     * @test
     */
    public function callsNewReport(): void
    {
        $creator = new DriverReportCreator(
            $this->sqlBasedDriverReportCreator,
            $this->oldDriverReportCreator,
            $this->createToggle([[
                'id' => 'driver_report_sql',
                'enabled' => true
            ]])
        );

        $this->sqlBasedDriverReportCreator->expects($this->once())->method('createReport')->with(self::DRIVER_ID, self::LAST_DAYS);

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
            $this->createToggle([[
                'id' => 'driver_report_sql',
                'enabled' => false
            ]])
        );

        $this->oldDriverReportCreator->expects($this->once())->method('createReport')->with(self::DRIVER_ID, self::LAST_DAYS);

        $creator->create(self::DRIVER_ID, self::LAST_DAYS);
    }

    private function createToggle(array $config): Toggle
    {
        return new Toggle(new InMemoryFeatureFinder(
            new InMemoryConfig($config),
            new InMemoryFeatureFactory(new ChainToggleStrategyFactory(
                new SegmentFactory()
            ))
        ));
    }
}
