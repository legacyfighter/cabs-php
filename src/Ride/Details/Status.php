<?php

namespace LegacyFighter\Cabs\Ride\Details;

class Status
{
    public const DRAFT = 'draft';
    public const CANCELLED = 'cancelled';
    public const WAITING_FOR_DRIVER_ASSIGNMENT = 'waiting-for-driver-assigment';
    public const DRIVER_ASSIGNMENT_FAILED = 'driver-assigment-failed';
    public const TRANSIT_TO_PASSENGER = 'transit-to-passenger';
    public const IN_TRANSIT = 'in-transit';
    public const COMPLETED = 'completed';

    private function __construct()
    {
    }
}
