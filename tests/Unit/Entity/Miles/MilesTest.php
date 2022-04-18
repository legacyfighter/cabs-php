<?php

namespace LegacyFighter\Cabs\Tests\Unit\Entity\Miles;

use LegacyFighter\Cabs\Entity\Miles\ConstantUntil;
use LegacyFighter\Cabs\Entity\Miles\TwoStepExpiringMiles;
use PHPUnit\Framework\TestCase;

class MilesTest extends TestCase
{
    /**
     * @test
     */
    public function milesWithoutExpirationDateDontExpire(): void
    {
        //given
        $neverExpiring = ConstantUntil::untilForever(10);

        //expect
        self::assertEquals(10, $neverExpiring->getAmountFor($this->yesterday()));
        self::assertEquals(10, $neverExpiring->getAmountFor($this->today()));
        self::assertEquals(10, $neverExpiring->getAmountFor($this->tomoroww()));
    }

    /**
     * @test
     */
    public function expiringMilesExpire(): void
    {
        //given
        $expiringMiles = ConstantUntil::until(10, $this->today());

        //expect
        self::assertEquals(10, $expiringMiles->getAmountFor($this->yesterday()));
        self::assertEquals(10, $expiringMiles->getAmountFor($this->today()));
        self::assertEquals(0, $expiringMiles->getAmountFor($this->tomoroww()));
    }

    /**
     * @test
     */
    public function canSubtractWhenEnoughMiles(): void
    {
        //given
        $expiringMiles = ConstantUntil::until(10, $this->today());
        $neverExpiring = ConstantUntil::untilForever(10);

        //expect
        self::assertEquals(ConstantUntil::until(0, $this->today()), $expiringMiles->subtract(10, $this->today()));
        self::assertEquals(ConstantUntil::until(0, $this->today()), $expiringMiles->subtract(10, $this->yesterday()));

        self::assertEquals(ConstantUntil::until(2, $this->today()), $expiringMiles->subtract(8, $this->today()));
        self::assertEquals(ConstantUntil::until(2, $this->today()), $expiringMiles->subtract(8, $this->yesterday()));

        self::assertEquals(ConstantUntil::untilForever(0), $neverExpiring->subtract(10, $this->yesterday()));
        self::assertEquals(ConstantUntil::untilForever(0), $neverExpiring->subtract(10, $this->today()));
        self::assertEquals(ConstantUntil::untilForever(0), $neverExpiring->subtract(10, $this->tomoroww()));

        self::assertEquals(ConstantUntil::untilForever(2), $neverExpiring->subtract(8, $this->yesterday()));
        self::assertEquals(ConstantUntil::untilForever(2), $neverExpiring->subtract(8, $this->today()));
        self::assertEquals(ConstantUntil::untilForever(2), $neverExpiring->subtract(8, $this->tomoroww()));
    }

    /**
     * @test
     */
    public function cannotSubtractWhenNotEnoughMiles(): void
    {
        //given
        $neverExpiring = ConstantUntil::untilForever(10);
        $expiringMiles = ConstantUntil::until(10, $this->today());

        //expect
        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $neverExpiring->subtract(11, $this->yesterday()));
        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $neverExpiring->subtract(11, $this->today()));
        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $neverExpiring->subtract(11, $this->tomoroww()));

        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $expiringMiles->subtract(11, $this->yesterday()));
        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $expiringMiles->subtract(11, $this->today()));
        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $expiringMiles->subtract(8, $this->tomoroww()));
        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $expiringMiles->subtract(8, $this->tomoroww()));
    }

    /**
     * @test
     */
    public function cannotSubtractFromTwoStepExpiringMiles(): void
    {
        //given
        $expiringInTwoStepsMiles = new TwoStepExpiringMiles(10, $this->yesterday(), $this->today());

        //expect
        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $expiringInTwoStepsMiles->subtract(11, $this->yesterday()));
        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $expiringInTwoStepsMiles->subtract(11, $this->today()));
        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $expiringInTwoStepsMiles->subtract(11, $this->tomoroww()));
        self::assertExceptionIsThrownBy(\InvalidArgumentException::class, fn() => $expiringInTwoStepsMiles->subtract(2, $this->tomoroww()));
    }

    /**
     * @test
     */
    public function twoStepExpiringMilesShouldLeaveHalfOfAmountAfterOneStep(): void
    {
        //given
        $twoStepExpiring = new TwoStepExpiringMiles(10, $this->yesterday(), $this->today());

        //expect
        self::assertEquals(10, $twoStepExpiring->getAmountFor($this->yesterday()));
        self::assertEquals(5, $twoStepExpiring->getAmountFor($this->today()));
        self::assertEquals(0, $twoStepExpiring->getAmountFor($this->tomoroww()));
    }

    /**
     * @test
     */
    public function canSubtractFromTwoStepExpiringMilesWhenEnoughMiles(): void
    {
        //given
        $twoStepExpiringOdd = new TwoStepExpiringMiles(9, $this->yesterday(), $this->today());
        $twoStepExpiringEven = new TwoStepExpiringMiles(10, $this->yesterday(), $this->today());

        //expect
        self::assertEquals(new TwoStepExpiringMiles(4, $this->yesterday(), $this->today()), $twoStepExpiringOdd->subtract(5, $this->yesterday()));
        self::assertEquals(new TwoStepExpiringMiles(1, $this->yesterday(), $this->today()), $twoStepExpiringOdd->subtract(4, $this->today()));

        self::assertEquals(new TwoStepExpiringMiles(5, $this->yesterday(), $this->today()), $twoStepExpiringEven->subtract(5, $this->yesterday()));
        self::assertEquals(new TwoStepExpiringMiles(0, $this->yesterday(), $this->today()), $twoStepExpiringEven->subtract(5, $this->today()));
    }

    private function yesterday(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('1989-12-12 12:12');
    }

    private function today(): \DateTimeImmutable
    {
        return $this->yesterday()->modify('+1 day');
    }

    private function tomoroww(): \DateTimeImmutable
    {
        return $this->today()->modify('+1 day');
    }

    private static function assertExceptionIsThrownBy(string $exception, callable $callable): void
    {
        $throwable = null;
        try {
            $callable();
        } catch (\Throwable $throwable) {
        }

        self::assertInstanceOf($exception, $throwable);
    }
}
