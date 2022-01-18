<?php

namespace LegacyFighter\Cabs\Ui;

use LegacyFighter\Cabs\Service\AwardsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AwardsAccountController
{
    public function __construct(private AwardsService $awardsService) {}

    #[Route('/clients/{clientId}/awards', methods: ['POST'])]
    public function register(int $clientId): Response
    {
        $this->awardsService->registerToProgram($clientId);
        return new JsonResponse($this->awardsService->findBy($clientId));
    }

    #[Route('/clients/{clientId}/awards/activate', methods: ['POST'])]
    public function activate(int $clientId): Response
    {
        $this->awardsService->activateAccount($clientId);
        return new JsonResponse($this->awardsService->findBy($clientId));
    }

    #[Route('/clients/{clientId}/awards/deactivate', methods: ['POST'])]
    public function deactivate(int $clientId): Response
    {
        $this->awardsService->deactivateAccount($clientId);
        return new JsonResponse($this->awardsService->findBy($clientId));
    }

    #[Route('/clients/{clientId}/awards/balance', methods: ['GET'])]
    public function balance(int $clientId): Response
    {
        return new JsonResponse($this->awardsService->calculateBalance($clientId));
    }

    #[Route('/clients/{clientId}/awards/transfer/{toClientId}/{howMuch}', methods: ['POST'])]
    public function transferMiles(int $clientId, int $toClientId, int $howMuch): Response
    {
        $this->awardsService->transferMiles($clientId, $toClientId, $howMuch);
        return new JsonResponse($this->awardsService->findBy($clientId));
    }

    #[Route('/clients/{clientId}/awards', methods: ['GET'])]
    public function findBy(int $clientId): Response
    {
        return new JsonResponse($this->awardsService->findBy($clientId));
    }
}
