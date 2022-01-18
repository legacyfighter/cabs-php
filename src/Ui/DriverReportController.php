<?php

namespace LegacyFighter\Cabs\Ui;

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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DriverReportController
{
    public function __construct(
        private DriverService $driverService,
        private DriverRepository $driverRepository,
        private ClaimRepository $claimRepository,
        private DriverSessionRepository $driverSessionRepository,
        private Clock $clock
    ) {}

    #[Route('/driverreport/{driverId}', methods: ['GET'])]
    public function loadReportForDriver(int $driverId, Request $request): Response
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
        $since = $beggingOfToday->modify(sprintf('-%s days', $request->get('lastDays', 1)));
        $allByDriverAndLoggedAtAfter = $this->driverSessionRepository->findAllByDriverAndLoggedAtAfter($driver, $since);
        $sessionsWithTransits = [];
        foreach ($allByDriverAndLoggedAtAfter as $session) {
            $dto = DriverSessionDTO::from($session);
            /** @var Transit[] $transitsInSession */
            $transitsInSession = array_filter(
                $driver->getTransits(),
                fn(Transit $transit) => $transit->getStatus() === Transit::STATUS_COMPLETED && $transit->getCompleteAt() < $session->getLoggedAt() && $transit->getCompleteAt() > $session->setLoggedOutAt()
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
        return new JsonResponse($driverReport);
    }
}
