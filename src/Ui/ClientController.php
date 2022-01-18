<?php

namespace LegacyFighter\Cabs\Ui;

use LegacyFighter\Cabs\DTO\ClientDTO;
use LegacyFighter\Cabs\Service\ClientService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController
{
    public function __construct(private ClientService $clientService) {}

    #[Route('/clients', methods: ['POST'])]
    public function register(ClientDTO $dto): Response
    {
        $c = $this->clientService->registerClient($dto->getName(), $dto->getLastName(), $dto->getType(), $dto->getDefaultPaymentType());
        return new JsonResponse($this->clientService->load($c->getId()));
    }

    #[Route('/clients/{clientId}', methods: ['GET'])]
    public function find(int $clientId): Response
    {
        return new JsonResponse($this->clientService->load($clientId));
    }

    #[Route('/clients/{clientId}/upgrade', methods: ['POST'])]
    public function upgradeToVIP(int $clientId): Response
    {
        $this->clientService->upgradeToVIP($clientId);
        return new JsonResponse($this->clientService->load($clientId));
    }

    #[Route('/clients/{clientId}/downgrade', methods: ['POST'])]
    public function downgrade(int $clientId): Response
    {
        $this->clientService->downgradeToRegular($clientId);
        return new JsonResponse($this->clientService->load($clientId));
    }

    #[Route('/clients/{clientId}/changeDefaultPaymentType', methods: ['POST'])]
    public function changeDefaultPaymentType(int $clientId, ClientDTO $clientDTO): Response
    {
        $this->clientService->changeDefaultPaymentType($clientId, $clientDTO->getDefaultPaymentType());
        return new JsonResponse($this->clientService->load($clientId));
    }
}
