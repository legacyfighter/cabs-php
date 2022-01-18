<?php

namespace LegacyFighter\Cabs\Ui;

use LegacyFighter\Cabs\DTO\ContractAttachmentDTO;
use LegacyFighter\Cabs\DTO\ContractDTO;
use LegacyFighter\Cabs\Service\ContractService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContractController
{
    public function __construct(private ContractService $contractService) {}

    #[Route('/contracts', methods: ['POST'])]
    public function create(ContractDTO $contractDTO): Response
    {
        $created = $this->contractService->createContract($contractDTO);
        return new JsonResponse(ContractDTO::from($created));
    }

    #[Route('/contracts/{id}', methods: ['GET'])]
    public function find(int $id): Response
    {
        $contract = $this->contractService->findDto($id);
        return new JsonResponse($contract);
    }

    #[Route('/contracts/{id}/attachment', methods: ['POST'])]
    public function proposeAttachment(int $id, ContractAttachmentDTO $contractAttachmentDTO): Response
    {
        $dto = $this->contractService->proposeAttachment($id, $contractAttachmentDTO);
        return new JsonResponse($dto);
    }

    #[Route('/contracts/{contractId}/attachment/{attachmentId}/reject', methods: ['POST'])]
    public function rejectAttachment(int $contractId, int $attachmentId): Response
    {
        $this->contractService->rejectAttachment($attachmentId);
        return new JsonResponse();
    }

    #[Route('/contracts/{contractId}/attachment/{attachmentId}/accept', methods: ['POST'])]
    public function acceptAttachment(int $contractId, int $attachmentId): Response
    {
        $this->contractService->acceptAttachment($attachmentId);
        return new JsonResponse();
    }

    #[Route('/contracts/{contractId}/attachment/{attachmentId}', methods: ['DELETE'])]
    public function removeAttachment(int $contractId, int $attachmentId): Response
    {
        $this->contractService->removeAttachment($contractId, $attachmentId);
        return new JsonResponse();
    }

    #[Route('/contracts/{id}/accept', methods: ['POST'])]
    public function acceptContract(int $id): Response
    {
        $this->contractService->acceptContract($id);
        return new JsonResponse();
    }

    #[Route('/contracts/{id}/reject', methods: ['POST'])]
    public function rejectContract(int $id): Response
    {
        $this->contractService->rejectContract($id);
        return new JsonResponse();
    }
}
