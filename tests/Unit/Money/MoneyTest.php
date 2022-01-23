<?php

namespace LegacyFighter\Cabs\Tests\Unit\Money;

use LegacyFighter\Cabs\Money\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    /**
     * @test
     */
    public function canCreateMoneyFromInteger(): void
    {
        //expect
        self::assertEquals('100.00', Money::from(10000)->toString());
        self::assertEquals('0.00', Money::from(0)->toString());
        self::assertEquals('10.12', Money::from(1012)->toString());
    }

    /**
     * @test
     */
    public function shouldProjectMoneyToInteger(): void
    {
        //expect
        self::assertEquals(10, Money::from(10)->toInt());
        self::assertEquals(0, Money::from(0)->toInt());
        self::assertEquals(-5, Money::from(-5)->toInt());
    }

    /**
     * @test
     */
    public function canAddMoney(): void
    {
        //expect
        self::assertEquals(Money::from(1000), Money::from(500)->add(Money::from(500)));
        self::assertEquals(Money::from(1042), Money::from(1020)->add(Money::from(22)));
        self::assertEquals(Money::from(0), Money::from(0)->add(Money::from(0)));
        self::assertEquals(Money::from(-2), Money::from(-4)->add(Money::from(2)));
    }

    /**
     * @test
     */
    public function canSubtractMoney(): void
    {
        //expect
        self::assertEquals(Money::zero(), Money::from(50)->subtract(Money::from(50)));
        self::assertEquals(Money::from(998), Money::from(1020)->subtract(Money::from(22)));
        self::assertEquals(Money::from(-1), Money::from(2)->subtract(Money::from(3)));
    }

    /**
     * @test
     */
    public function canCalculatePercentage(): void
    {
        self::assertEquals('30.00', Money::from(10000)->percentage(30)->toString());
        self::assertEquals('26.40', Money::from(8800)->percentage(30)->toString());
        self::assertEquals('88.00', Money::from(8800)->percentage(100)->toString());
        self::assertEquals('0.00', Money::from(8800)->percentage(0)->toString());
        self::assertEquals('13.20', Money::from(4400)->percentage(30)->toString());
        self::assertEquals('0.30', Money::from(100)->percentage(30)->toString());
        self::assertEquals('0.00', Money::from(1)->percentage(40)->toString());
    }
}
