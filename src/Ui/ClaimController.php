<?php

namespace LegacyFighter\Cabs\Ui;

use LegacyFighter\Cabs\DTO\ClaimDTO;
use LegacyFighter\Cabs\Entity\Claim;
use LegacyFighter\Cabs\Service\ClaimService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClaimController
{
    public function __construct(private ClaimService $claimService) {}

    #[Route('/claims/createDraft', methods: ['POST'])]
    public function create(ClaimDTO $claimDTO): Response
    {
        $created = $this->claimService->create($claimDTO);
        return new JsonResponse(ClaimDTO::from($created));
    }

    #[Route('/claims/send', methods: ['POST'])]
    public function sendNew(ClaimDTO $claimDTO): Response
    {
        $claimDTO->setIsDraft(false);
        $claim = $this->claimService->create($claimDTO);
        return new JsonResponse(ClaimDTO::from($claim));
    }

    #[Route('/claims/{id}/markInProcess', methods: ['POST'])]
    public function markAsInProcess(int $id): Response
    {
        $claim = $this->claimService->setStatus(Claim::STATUS_IN_PROCESS, $id);
        return new JsonResponse(ClaimDTO::from($claim));
    }

    #[Route('/claims/{id}', methods: ['GET'])]
    public function find(int $id): Response
    {
        $claim = $this->claimService->find($id);
        return new JsonResponse(ClaimDTO::from($claim));
    }

    #[Route('/claims/{id}', methods: ['POST'])]
    public function tryToAutomaticallyResolve(int $id): Response
    {
        $claim = $this->claimService->tryToResolveAutomatically($id);
        return new JsonResponse(ClaimDTO::from($claim));
    }
}
