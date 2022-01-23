<?php

namespace LegacyFighter\Cabs\Tests\Unit\Distance;

use LegacyFighter\Cabs\Distance\Distance;
use PHPUnit\Framework\TestCase;

class DistanceTest extends TestCase
{
    /**
     * @test
     */
    public function cannotUnderstandInvalidUnit(): void
    {
        //expect
        $this->expectException(\InvalidArgumentException::class);
        Distance::ofKm(2000.0)->printIn('invalid');
    }

    /**
     * @test
     */
    public function canConvertToFloat(): void
    {
        //expect
        self::assertEquals(2000, Distance::ofKm(2000)->toKmInFloat());
        self::assertEquals(0, Distance::ofKm(0)->toKmInFloat());
        self::assertEquals(312.22, Distance::ofKm(312.22)->toKmInFloat());
        self::assertEquals(2, Distance::ofKm(2)->toKmInFloat());
    }

    /**
     * @test
     */
    public function canRepresentDistanceAsMeters(): void
    {
        //expect
        self::assertEquals('2000000m', Distance::ofKm(2000)->printIn('m'));
        self::assertEquals('0m', Distance::ofKm(0)->printIn('m'));
        self::assertEquals('312220m', Distance::ofKm(312.22)->printIn('m'));
        self::assertEquals('2000m', Distance::ofKm(2)->printIn('m'));
    }

    /**
     * @test
     */
    public function canRepresentDistanceAsKm(): void
    {
        //expect
        self::assertEquals('2000km', Distance::ofKm(2000)->printIn('km'));
        self::assertEquals('0km', Distance::ofKm(0)->printIn('km'));
        self::assertEquals('312.220km', Distance::ofKm(312.22)->printIn('km'));
        self::assertEquals('312.221km', Distance::ofKm(312.221111232313)->printIn('km'));
        self::assertEquals('2km', Distance::ofKm(2)->printIn('km'));
    }

    /**
     * @test
     */
    public function canRepresentDistanceAsMiles(): void
    {
        //expect
        self::assertEquals('1242.742miles', Distance::ofKm(2000)->printIn('miles'));
        self::assertEquals('0miles', Distance::ofKm(0)->printIn('miles'));
        self::assertEquals('194.005miles', Distance::ofKm(312.22)->printIn('miles'));
        self::assertEquals('194.005miles', Distance::ofKm(312.221111232313)->printIn('miles'));
        self::assertEquals('1.243miles', Distance::ofKm(2)->printIn('miles'));
    }
}
