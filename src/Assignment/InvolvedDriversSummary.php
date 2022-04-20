<?php

namespace LegacyFighter\Cabs\Assignment;

class InvolvedDriversSummary
{
    public function __construct(
        /**
         * @var int[]
         */
        public array $proposedDrivers,

        /**
         * @var int[]
         */
        public array $driverRejections,

        public ?int $assignedDriver,

        public string $status
    ) {

    }

    public static function noneFound(): self
    {
        return new self([], [], null, AssignmentStatus::DRIVER_ASSIGNMENT_FAILED);
    }
}
