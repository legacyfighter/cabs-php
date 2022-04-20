<?php

namespace LegacyFighter\Cabs\Assignment;

final class AssignmentStatus
{
    public const CANCELLED = 'cancelled';
    public const WAITING_FOR_DRIVER_ASSIGNMENT = 'waiting-for-driver-assignment';
    public const DRIVER_ASSIGNMENT_FAILED = 'driver-assigment-failed';
    public const ON_THE_WAY = 'on-the-way';

    private function __construct()
    {
    }
}
