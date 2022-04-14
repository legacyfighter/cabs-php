<?php

namespace LegacyFighter\Cabs\DriverReport;

use Doctrine\DBAL\Connection;
use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\DTO\AddressDTO;
use LegacyFighter\Cabs\DTO\ClaimDTO;
use LegacyFighter\Cabs\DTO\DriverDTO;
use LegacyFighter\Cabs\DTO\DriverReport;
use LegacyFighter\Cabs\DTO\DriverSessionDTO;
use LegacyFighter\Cabs\DTO\TransitDTO;
use LegacyFighter\Cabs\Entity\DriverAttribute;
use LegacyFighter\Cabs\Entity\Transit;

class SqlBasedDriverReportCreator
{
    private const QUERY_FOR_DRIVER_WITH_ATTRS =
            'SELECT d.id, d.first_name, d.last_name, d.driver_license, ' .
                    'd.photo, d.status, d.type, attr.name, attr.value ' .
            'FROM Driver d ' .
            'LEFT JOIN driver_attribute attr ON d.id = attr.driver_id ' .
            'WHERE d.id = :driverId AND attr.name <> :filteredAttr';

    private const QUERY_FOR_SESSIONS = 'SELECT ds.id AS SESSION_ID, ds.logged_at, ds.logged_out_at, ds.plates_number, ds.car_class, ds.car_brand, ' .
            'td.transit_id as TRANSIT_ID, td.tariff_name as TARIFF_NAME, td.status as TRANSIT_STATUS, td.distance, td.tariff_km_rate, ' .
            'td.price, td.drivers_fee, td.estimated_price, td.tariff_base_fee, ' .
            'td.date_time, td.published_at, td.accepted_at, td.started, td.completed_at, td.car_type, ' .
            'cl.id as CLAIM_ID, cl.owner_id, cl.reason, cl.incident_description, cl.status as CLAIM_STATUS, cl.creation_date, ' .
            'cl.completion_date, cl.change_date, cl.completion_mode, cl.claim_no, ' .
            'af.country as AF_COUNTRY, af.city as AF_CITY, af.street AS AF_STREET, af.building_number AS AF_NUMBER, ' .
            'ato.country as ATO_COUNTRY, ato.city as ATO_CITY, ato.street AS ATO_STREET, ato.building_number AS ATO_NUMBER ' .
            'FROM driver_session ds ' .
            'LEFT JOIN transit_details td ON td.driver_id = ds.driver_id ' .
            'LEFT JOIN Address af ON td.from_id = af.id ' .
            'LEFT JOIN Address ato ON td.to_id = ato.id ' .
            'LEFT JOIN claim cl ON cl.transit_id = td.transit_id ' .
            'WHERE ds.driver_id = :driverId AND td.status = :transitStatus ' .
            'AND ds.logged_at >= :since ' .
            'AND td.completed_at >= ds.logged_at ' .
            'AND td.completed_at <= ds.logged_out_at';

    private Connection $connection;
    private Clock $clock;

    public function __construct(Connection $connection, Clock $clock)
    {
        $this->connection = $connection;
        $this->clock = $clock;
    }

    public function createReport(int $driverId, int $lastDays): DriverReport
    {
        $driverReport = new DriverReport();
        $driverInfo = $this->connection->fetchAllAssociative(self::QUERY_FOR_DRIVER_WITH_ATTRS, [
            'driverId' => $driverId,
            'filteredAttr' => DriverAttribute::NAME_MEDICAL_EXAMINATION_REMARKS
        ]);
        $this->addAttrToReport($driverReport, $driverInfo);
        $this->addDriverToReport($driverReport, $driverInfo[0]);

        $sessions = $this->connection->fetchAllAssociative(self::QUERY_FOR_SESSIONS, [
            'driverId' => $driverId,
            'transitStatus' => Transit::STATUS_COMPLETED,
            'since' =>  $this->calculateStartingPoint($lastDays)->format('Y-m-d H:i:s')
        ]);
        $this->addSessionsToReport($driverReport, $sessions);

        return $driverReport;
    }

    private function calculateStartingPoint(int $lastDays): \DateTimeImmutable
    {
        return $this->clock->now()->setTime(0, 0)->modify(sprintf('-%s days', $lastDays));
    }

    private function addAttrToReport(DriverReport $driverReport, array $driverInfo): void
    {
        array_map(fn(array $data) => $driverReport->addAttr($data['name'], $data['value']), $driverInfo);
    }

    private function addDriverToReport(DriverReport $driverReport, array $driverInfo): void
    {
        $driverReport->setDriverDTO(DriverDTO::with(
            $driverInfo['id'],
            $driverInfo['first_name'],
            $driverInfo['last_name'],
            $driverInfo['driver_license'],
            $driverInfo['photo'],
            $driverInfo['status'],
            $driverInfo['type']
        ));
    }

    private function addSessionsToReport(DriverReport $driverReport, array $sessions): void
    {
        $data = [];
        foreach ($sessions as $session) {
            if(!isset($data[$session['session_id']])) {
                $data[$session['session_id']] = [
                    'session' => DriverSessionDTO::with(
                        new \DateTimeImmutable($session['logged_at']),
                        $session['logged_out_at'] !== null ? new \DateTimeImmutable($session['logged_out_at']) : null,
                        $session['plates_number'],
                        $session['car_class'],
                        $session['car_brand']
                    ),
                    'transits' => []
                ];
            }

            $data[$session['session_id']]['transits'][] = TransitDTO::with(
                $session['transit_id'],
                $session['transit_status'],
                $session['tariff_name'],
                $session['tariff_km_rate'],
                AddressDTO::with($session['af_country'], $session['af_city'], $session['af_street'], $session['af_number']),
                AddressDTO::with($session['ato_country'], $session['ato_city'], $session['ato_street'], $session['ato_number']),
                null,
                null,
                $session['reason'] !== null ? ClaimDTO::with(
                    $session['incident_description'],
                    $session['reason'],
                    $session['owner_id'],
                    $session['transit_id'],
                    $session['claim_id'],
                    $session['claim_status'],
                    $session['completion_mode'],
                    $session['claim_no']
                ) : null
            );
        }
        $driverReport->setSessions(array_values($data));
    }
}
