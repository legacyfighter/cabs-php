<?php

namespace LegacyFighter\Cabs\Crm\TransitAnalyzer;

use LegacyFighter\Cabs\DTO\AnalyzedAddressesDTO;
use LegacyFighter\Cabs\Geolocation\Address\AddressDTO;
use LegacyFighter\Cabs\Geolocation\Address\AddressRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransitAnalyzerController
{
    public function __construct(
        private GraphTransitAnalyzer $analyzer,
        private AddressRepository $addressRepository
    ) {}

    #[Route('/transitAnalyze/{clientId}/{addressId}', methods: ['GET'])]
    public function analyze(int $clientId, int $addressId): Response
    {
        $addresses = $this->analyzer->analyze($clientId, $this->addressRepository->findHashById($addressId));
        return new JsonResponse(new AnalyzedAddressesDTO(array_map(
            fn (int $hash) => AddressDTO::from($this->addressRepository->getByHash($hash)),
            $addresses
        )));
    }
}
