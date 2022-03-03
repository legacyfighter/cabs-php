<?php

namespace LegacyFighter\Cabs\Tests\Unit\Entity;

use LegacyFighter\Cabs\Entity\TimeSlot;
use PHPUnit\Framework\TestCase;

class TimeSlotTest extends TestCase
{
    /**
     * @test
     * @dataProvider beginningMustBeBeforeEndDataProvider
     */
    public function beginningMustBeBeforeEnd(\DateTimeImmutable $beginning, \DateTimeImmutable $end): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TimeSlot::of($beginning, $end);
    }

    public function beginningMustBeBeforeEndDataProvider(): array
    {
        return [
            [$this->noonFive(), $this->noon()],
            [$this->noonTen(), $this->noon()],
            [$this->noonTen(), $this->noonFive()],
            [$this->noonTen(), $this->noonTen()]
        ];
    }

    /**
     * @test
     */
    public function canCreateValidSlot(): void
    {
        //given
        $noonToFive = TimeSlot::of($this->noon(), $this->noonFive());
        $fiveToTen = TimeSlot::of($this->noonFive(), $this->noonTen());

        //expect
        self::assertEquals($this->noon(), $noonToFive->beginning());
        self::assertEquals($this->noonFive(), $noonToFive->end());
        self::assertEquals($this->noonFive(), $fiveToTen->beginning());
        self::assertEquals($this->noonTen(), $fiveToTen->end());
    }

    /**
     * @test
     */
    public function canCreatePreviousSLot(): void
    {
        //given
        $noonToFive = TimeSlot::of($this->noon(), $this->noonFive());
        $fiveToTen = TimeSlot::of($this->noonFive(), $this->noonTen());
        $tenToFifteen = TimeSlot::of($this->noonTen(), $this->noonTen()->modify('+5 minutes'));

        //expect
        self::assertEquals($noonToFive, $fiveToTen->prev());
        self::assertEquals($fiveToTen, $tenToFifteen->prev());
        self::assertEquals($noonToFive, $tenToFifteen->prev()->prev());
    }

    /**
     * @test
     */
    public function canCalculateIfTimestampIsWithin(): void
    {
        //given
        $noonToFive = TimeSlot::of($this->noon(), $this->noonFive());
        $fiveToTen = TimeSlot::of($this->noonFive(), $this->noonTen());

        //expect
        self::assertTrue($noonToFive->contains($this->noon()));
        self::assertTrue($noonToFive->contains($this->noon()->modify('+1 minute')));
        self::assertFalse($noonToFive->contains($this->noonFive()));
        self::assertFalse($noonToFive->contains($this->noonFive()->modify('+1 minute')));

        self::assertFalse($noonToFive->isBefore($this->noon()));
        self::assertFalse($noonToFive->isBefore($this->noonFive()));
        self::assertTrue($noonToFive->isBefore($this->noonTen()));

        self::assertTrue($noonToFive->endsAt($this->noonFive()));

        self::assertFalse($fiveToTen->contains($this->noon()));
        self::assertTrue($fiveToTen->contains($this->noonFive()));
        self::assertTrue($fiveToTen->contains($this->noonFive()->modify('+1 minute')));
        self::assertFalse($fiveToTen->contains($this->noonTen()));
        self::assertFalse($fiveToTen->contains($this->noonTen()->modify('+1 minute')));

        self::assertFalse($fiveToTen->isBefore($this->noon()));
        self::assertFalse($fiveToTen->isBefore($this->noonFive()));
        self::assertFalse($fiveToTen->isBefore($this->noonTen()));
        self::assertTrue($fiveToTen->isBefore($this->noonTen()->modify('+1 minute')));

        self::assertTrue($fiveToTen->endsAt($this->noonTen()));
    }

    /**
     * @test
     */
    public function canCreateSlotFromSeedWithinThatSlot(): void
    {
        //given
        $noonToFive = TimeSlot::of($this->noon(), $this->noonFive());
        $fiveToTen = TimeSlot::of($this->noonFive(), $this->noonTen());

        //expect
        self::assertEquals($noonToFive, TimeSlot::slotThatContains($this->noon()->modify('+1 minute')));
        self::assertEquals($noonToFive, TimeSlot::slotThatContains($this->noon()->modify('+2 minute')));
        self::assertEquals($noonToFive, TimeSlot::slotThatContains($this->noon()->modify('+3 minute')));
        self::assertEquals($noonToFive, TimeSlot::slotThatContains($this->noon()->modify('+4 minute')));

        self::assertEquals($fiveToTen, TimeSlot::slotThatContains($this->noonFive()->modify('+1 minute')));
        self::assertEquals($fiveToTen, TimeSlot::slotThatContains($this->noonFive()->modify('+2 minute')));
        self::assertEquals($fiveToTen, TimeSlot::slotThatContains($this->noonFive()->modify('+3 minute')));
    }

    private function noon(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('1989-12-12 12:10');
    }

    private function noonFive(): \DateTimeImmutable
    {
        return $this->noon()->modify('+5 minutes');
    }

    private function noonTen(): \DateTimeImmutable
    {
        return $this->noon()->modify('+10 minutes');
    }
}
