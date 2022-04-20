<?php

namespace LegacyFighter\Cabs\Tests\Unit\Assigment;

use LegacyFighter\Cabs\Assignment\AssignmentStatus;
use LegacyFighter\Cabs\Assignment\DriverAssignment;
use LegacyFighter\Cabs\Ride\Details\Status;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class DriverAssignmentTest extends TestCase
{
    private const DRIVER_ID = 1;
    private const SECOND_DRIVER_ID = 2;

    /**
     * @test
     */
    public function canAcceptTransit(): void
    {
        //given
        $assignment = $this->assigmentForTransit(new \DateTimeImmutable());
        //and
        $assignment->proposeTo(self::DRIVER_ID);

        //when
        $assignment->acceptBy(self::DRIVER_ID);
        //then
        self::assertEquals(AssignmentStatus::ON_THE_WAY, $assignment->getStatus());
    }

    /**
     * @test
     */
    public function onlyOneDriverCanAcceptTransit(): void
    {
        //given
        $assignment = $this->assigmentForTransit(new \DateTimeImmutable());
        //and
        $assignment->proposeTo(self::DRIVER_ID);

        //then
        $this->expectException(\RuntimeException::class);
        //when
        $assignment->acceptBy(self::SECOND_DRIVER_ID);
    }

    /**
     * @test
     */
    public function transitCannotByAcceptedByDriverWhoAlreadyRejected(): void
    {
        //given
        $assignment = $this->assigmentForTransit(new \DateTimeImmutable());
        //and
        $assignment->rejectBy(self::DRIVER_ID);

        //then
        $this->expectException(\RuntimeException::class);
        //when
        $assignment->acceptBy(self::DRIVER_ID);
    }

    /**
     * @test
     */
    public function transitCannotByAcceptedByDriverWhoHasNotSeenProposal(): void
    {
        //given
        $assignment = $this->assigmentForTransit(new \DateTimeImmutable());

        //then
        $this->expectException(\RuntimeException::class);
        //when
        $assignment->acceptBy(self::DRIVER_ID);
    }

    /**
     * @test
     */
    public function canRejectTransit(): void
    {
        //given
        $assignment = $this->assigmentForTransit(new \DateTimeImmutable());
        //and
        $assignment->rejectBy(self::DRIVER_ID);

        //then
        self::assertEquals(AssignmentStatus::WAITING_FOR_DRIVER_ASSIGNMENT, $assignment->getStatus());
    }

    private function assigmentForTransit(\DateTimeImmutable $when): DriverAssignment
    {
        return new DriverAssignment(Uuid::v4(), $when);
    }
}
