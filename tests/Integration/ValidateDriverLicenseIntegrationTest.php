<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\DriverFleet\DriverDTO;
use LegacyFighter\Cabs\DriverFleet\DriverService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ValidateDriverLicenseIntegrationTest extends KernelTestCase
{
    private DriverService $driverService;

    protected function setUp(): void
    {
        $this->driverService = $this->getContainer()->get(DriverService::class);
    }

    /**
     * @test
     */
    public function cannotCreateActiveDriverWithInvalidLicense(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->createActiveDriverWithLicense('invalidLicense');
    }

    /**
     * @test
     */
    public function canCreateActiveDriverWithValidLicense(): void
    {
        // when
        $driver = $this->createActiveDriverWithLicense('FARME100165AB5EW');

        // then
        $loaded = $this->load($driver);
        self::assertEquals('FARME100165AB5EW', $loaded->getDriverLicense());
        self::assertEquals(Driver::STATUS_ACTIVE, $loaded->getStatus());
    }

    /**
     * @test
     */
    public function canCreateInactiveDriverWithInvalidLicense(): void
    {
        // when
        $driver = $this->createInactiveDriverWithLicense('invalidlicense');

        // then
        $loaded = $this->load($driver);
        self::assertEquals('invalidlicense', $loaded->getDriverLicense());
        self::assertEquals(Driver::STATUS_INACTIVE, $loaded->getStatus());
    }

    /**
     * @test
     */
    public function canChangeLicenseForValidOne(): void
    {
        // given
        $driver = $this->createActiveDriverWithLicense('FARME100165AB5EW');

        // when
        $this->changeLicenseTo('99999740614992TL', $driver);

        // then
        $loaded = $this->load($driver);
        self::assertEquals('99999740614992TL', $loaded->getDriverLicense());
    }

    /**
     * @test
     */
    public function cannotChangeLicenseForInvalidOne(): void
    {
        // given
        $driver = $this->createActiveDriverWithLicense('FARME100165AB5EW');

        // then
        $this->expectException(\InvalidArgumentException::class);

        // when
        $this->changeLicenseTo('invalid', $driver);
    }

    /**
     * @test
     */
    public function canActivateDriverWithValidLicense(): void
    {
        // given
        $driver = $this->createInactiveDriverWithLicense('FARME100165AB5EW');

        // when
        $this->activate($driver);

        // then
        $loaded = $this->load($driver);
        self::assertEquals(Driver::STATUS_ACTIVE, $loaded->getStatus());
    }

    /**
     * @test
     */
    public function cannotActivateDriverWithInvalidLicense(): void
    {
        // given
        $driver = $this->createInactiveDriverWithLicense('invalid');

        // then
        $this->expectException(\InvalidArgumentException::class);

        // when
        $this->activate($driver);
    }

    private function createActiveDriverWithLicense(string $license): Driver
    {
        return $this->driverService->createDriver($license, 'Kowalski', 'Jan', Driver::TYPE_REGULAR, Driver::STATUS_ACTIVE, 'photo');
    }

    private function createInactiveDriverWithLicense(string $license): Driver
    {
        return $this->driverService->createDriver($license, 'Kowalski', 'Jan', Driver::TYPE_REGULAR, Driver::STATUS_INACTIVE, 'photo');
    }

    private function load(Driver $driver): DriverDTO
    {
        return $this->driverService->load($driver->getId());
    }

    private function changeLicenseTo(string $newLicense, Driver $driver): void
    {
        $this->driverService->changeLicenseNumber($newLicense, $driver->getId());
    }

    private function activate(Driver $driver): void
    {
        $this->driverService->changeDriverStatus($driver->getId(), Driver::STATUS_ACTIVE);
    }
}
