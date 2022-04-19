<?php

namespace LegacyFighter\Cabs\Tests\Unit\Entity;

use LegacyFighter\Cabs\DriverFleet\DriverLicense;
use PHPUnit\Framework\TestCase;

class DriverLicenseTest extends TestCase
{
    /**
     * @test
     */
    public function cannotCreateInvalidLicense(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DriverLicense::withLicense('invalid');
    }

    /**
     * @test
     */
    public function cannotCreateEmptyLicense(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DriverLicense::withLicense('');
    }

    /**
     * @test
     */
    public function canCreateValidLicense(): void
    {
        //when
        $license = DriverLicense::withLicense('FARME100165AB5EW');

        //then
        self::assertEquals('FARME100165AB5EW', $license->asString());
    }

    /**
     * @test
     */
    public function canCreateInvalidLicenseExplicitly(): void
    {
        //when
        $license = DriverLicense::withoutValidation('invalid');

        //then
        self::assertEquals('invalid', $license->asString());
    }
}
