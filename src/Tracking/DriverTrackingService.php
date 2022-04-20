<?php

namespace LegacyFighter\Cabs\Tracking;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\DriverFleet\DriverDTO;
use LegacyFighter\Cabs\DriverFleet\DriverReport\TravelledDistance\TravelledDistanceService;
use LegacyFighter\Cabs\DriverFleet\DriverService;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Distance;
use LegacyFighter\Cabs\Geolocation\GeocodingService;

class DriverTrackingService
{
    public function __construct(
        private DriverPositionRepository $driverPositionRepository,
        private DriverService $driverService,
        private TravelledDistanceService $travelledDistanceService,
        private DriverSessionService $driverSessionService,
        private Clock $clock,
        private GeocodingService $geocodingService
    )
    {
    }

    public function registerPosition(int $driverId, float $latitude, float $longitude, \DateTimeImmutable $seenAt): DriverPosition
    {
        $driver = $this->driverService->load($driverId);
        if($driver->getStatus() !== Driver::STATUS_ACTIVE) {
            throw new \InvalidArgumentException('Driver is not active, cannot register position, id = '.$driverId);
        }
        $driverPosition = $this->driverPositionRepository->save(new DriverPosition($driverId, $latitude, $longitude, $seenAt));
        $this->travelledDistanceService->addPosition($driverId, $latitude, $longitude, $seenAt);
        return $driverPosition;
    }

    public function calculateTravelledDistance(int $driverId, \DateTimeImmutable $from, \DateTimeImmutable $to): Distance
    {
        return $this->travelledDistanceService->calculateDistance($driverId, $from, $to);
    }

    /**
     * @param string[] $carClasses
     * @return DriverPositionDTOV2[]
     */
    public function findActiveDriversNearby(float $latitudeMin, float $latitudeMax, float $longitudeMin, float $longitudeMax, float $latitude, float $longitude, array $carClasses): array
    {
        $driversAvgPositions = $this->driverPositionRepository->findAverageDriverPositionSince($latitudeMin, $latitudeMax, $longitudeMin, $longitudeMax, $this->clock->now()->modify('-5 minutes'));
        usort(
            $driversAvgPositions,
            fn(DriverPositionDTOV2 $d1, DriverPositionDTOV2 $d2) =>
                sqrt(pow($latitude - $d1->getLatitude(), 2) + pow($longitude - $d1->getLongitude(), 2)) <=>
                sqrt(pow($latitude - $d2->getLatitude(), 2) + pow($longitude - $d2->getLongitude(), 2))
        );
        $driversAvgPositions = array_slice($driversAvgPositions, 0, 20);
        $driversIds = array_map(fn(DriverPositionDTOV2 $d) => $d->getDriverId(), $driversAvgPositions);
        $activeDriverIdsInSpecificCar = $this->driverSessionService->findCurrentlyLoggedDriverIds($driversIds, $carClasses);

        $driversAvgPositions = array_filter(
            $driversAvgPositions,
            fn(DriverPositionDTOV2 $dp) => in_array($dp->getDriverId(), $activeDriverIdsInSpecificCar)
        );
        /** @var DriverDTO[] $drivers */
        $drivers = [];
        foreach ($this->driverService->loadDrivers($driversIds) as $driver) {
            $drivers[$driver->getId()] = $driver;
        }

        return array_filter(
            $driversAvgPositions,
            fn(DriverPositionDTOV2 $dp) => $drivers[$dp->getDriverId()]->getStatus() === Driver::STATUS_ACTIVE && !$drivers[$dp->getDriverId()]->isOccupied()
        );
    }

    /**
     * @param string[] $carClasses
     * @return DriverPositionDTOV2[]
     */
    public function findActiveDriversNearbyAddress(AddressDTO $address, Distance $distance, array $carClasses): array
    {
        $geocoded = [];
        try {
            $geocoded = $this->geocodingService->geocodeAddress($address->toAddressEntity());
        } catch (\Throwable $exception) {
            // Geocoding failed! Ask Jessica or Bryan for some help if needed.
        }

        $longitude = $geocoded[1];
        $latitude = $geocoded[0];

        //https://gis.stackexchange.com/questions/2951/algorithm-for-offsetting-a-latitude-longitude-by-some-amount-of-meters
        //Earth’s radius, sphere
        //double R = 6378;
        $R = 6371; // Changed to 6371 due to Copy&Paste pattern from different source

        //offsets in meters
        $dn = $distance->toKmInFloat();
        $de = $distance->toKmInFloat();

        //Coordinate offsets in radians
        $dLat = $dn / $R;
        $dLon = $de / ($R * cos(M_PI * $latitude / 180));

        //Offset positions, decimal degrees
        $latitudeMin = $latitude - $dLat * 180 / M_PI;
        $latitudeMax = $latitude + $dLat *
            180 / M_PI;
        $longitudeMin = $longitude - $dLon *
            180 / M_PI;
        $longitudeMax = $longitude + $dLon * 180 / M_PI;

        return $this->findActiveDriversNearby($latitudeMin, $latitudeMax, $longitudeMin, $longitudeMax, $latitude, $longitude, $carClasses);
    }
}
