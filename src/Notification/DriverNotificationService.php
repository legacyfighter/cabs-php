<?php

namespace LegacyFighter\Cabs\Notification;

use Symfony\Component\Uid\Uuid;

class DriverNotificationService
{
    public function notifyAboutPossibleTransit(int $driverId, int $transitId): void
    {

    }

    public function notifyAboutPossibleTransitBy(int $driverId, Uuid $requestId) {
        // find transit and delegate to notifyAboutPossibleTransit(long, long)
    }

    public function notifyAboutChangedTransitAddressBy(int $driverId, Uuid $requestId): void {
        // find transit and delegate to notifyAboutChangedTransitAddress(long, long)
    }

    public function  notifyAboutCancelledTransitBy(int $driverId, Uuid $requestId) {
        // find transit and delegate to notifyAboutCancelledTransit(long, long)
    }


    public function notifyAboutChangedTransitAddress(int $driverId, int $transitId): void
    {

    }

    public function notifyAboutCancelledTransit(int $driverId, int $transitId): void
    {

    }

    public function askDriverForDetailsAboutClaim(string $claimNo, int $driverId): void
    {

    }
}
