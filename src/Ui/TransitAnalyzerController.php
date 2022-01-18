<?php

namespace LegacyFighter\Cabs\Ui;

use LegacyFighter\Cabs\DTO\AddressDTO;
use LegacyFighter\Cabs\DTO\AnalyzedAddressesDTO;
use LegacyFighter\Cabs\Entity\Address;
use LegacyFighter\Cabs\Service\TransitAnalyzer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransitAnalyzerController
{
    public function __construct(private TransitAnalyzer $transitAnalyzer) {}

    #[Route('/transitAnalyze/{clientId}/{addressId}', methods: ['GET'])]
    public function analyze(int $clientId, int $addressId): Response
    {
        $addresses = $this->transitAnalyzer->analyze($clientId, $addressId);
        return new JsonResponse(new AnalyzedAddressesDTO(array_map(
            fn(Address $a) => AddressDTO::from($a),
            $addresses
        )));
    }
}
