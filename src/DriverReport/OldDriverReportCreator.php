<?php

namespace LegacyFighter\Cabs\DriverReport;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\DTO\ClaimDTO;
use LegacyFighter\Cabs\DTO\DriverAttributeDTO;
use LegacyFighter\Cabs\DTO\DriverReport;
use LegacyFighter\Cabs\DTO\DriverSessionDTO;
use LegacyFighter\Cabs\DTO\TransitDTO;
use LegacyFighter\Cabs\Entity\DriverAttribute;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Repository\ClaimRepository;
use LegacyFighter\Cabs\Repository\DriverRepository;
use LegacyFighter\Cabs\Repository\DriverSessionRepository;
use LegacyFighter\Cabs\Service\DriverService;

class OldDriverReportCreator
{
    public function __construct(
        private DriverService $driverService,
        private DriverRepository $driverRepository,
        private DriverSessionRepository $driverSessionRepository,
        private ClaimRepository $claimRepository,
        private Clock $clock
    ) {}

    public function createReport(int $driverId, int $lastDays): DriverReport
    {
        $driverReport = new DriverReport();
        $driverDTO = $this->driverService->load($driverId);
        $driverReport->setDriverDTO($driverDTO);
        $driver = $this->driverRepository->getOne($driverId);
        $driverReport->setAttributes(
            array_map(
                fn(DriverAttribute $a) => DriverAttributeDTO::from($a),
                array_filter(
                    $driver->getAttributes(),
                    fn(DriverAttribute $a) => $a->getName() !== DriverAttribute::NAME_MEDICAL_EXAMINATION_REMARKS
                )
            )
        );
        $beggingOfToday = $this->clock->now()->setTime(0, 0);
        $since = $beggingOfToday->modify(sprintf('-%s days', $lastDays));
        $allByDriverAndLoggedAtAfter = $this->driverSessionRepository->findAllByDriverAndLoggedAtAfter($driver, $since);
        $sessionsWithTransits = [];
        foreach ($allByDriverAndLoggedAtAfter as $session) {
            $dto = DriverSessionDTO::from($session);
            /** @var Transit[] $transitsInSession */
            $transitsInSession = array_filter(
                $driver->getTransits(),
                fn(Transit $transit) => $transit->getStatus() === Transit::STATUS_COMPLETED && $transit->getCompleteAt() >= $session->getLoggedAt() && $transit->getCompleteAt() <= $session->getLoggedOutAt()
            );
            $transitsDtosInSession = [];
            foreach ($transitsInSession as $t) {
                $transitDTO = TransitDTO::from($t);
                $byOwnerAndTransit = $this->claimRepository->findByOwnerAndTransit($t->getClient(), $t);
                if($byOwnerAndTransit !== []) {
                    $claim = ClaimDTO::from($byOwnerAndTransit[0]);
                    $transitDTO->setClaimDTO($claim);
                }
                $transitsDtosInSession[] = $transitDTO;
            }
            $sessionsWithTransits[] = ['session' => $dto, 'transits' => $transitsDtosInSession];
        }
        $driverReport->setSessions($sessionsWithTransits);

        return $driverReport;
    }
}
