<?php

namespace LegacyFighter\Cabs\Repair\Api;

use LegacyFighter\Cabs\Party\Api\PartyId;
use LegacyFighter\Cabs\Party\Model\Party\PartyRelationshipRepository;
use LegacyFighter\Cabs\Party\Model\Party\PartyRepository;
use LegacyFighter\Cabs\Repair\Model\Dict\PartyRelationshipsDictionary;
use LegacyFighter\Cabs\Repair\Model\Dict\PartyRolesDictionary;

class ContractManager
{
    public function __construct(
        private PartyRepository $partyRepository,
        private PartyRelationshipRepository $partyRelationshipRepository
    ) {}

    public function extendedWarrantyContractSigned(PartyId $insurerId, PartyId $vehicleId): void
    {
        $insurer = $this->partyRepository->put($insurerId->toUuid());
        $vehicle = $this->partyRepository->put($vehicleId->toUuid());

        $this->partyRelationshipRepository->put(
            PartyRelationshipsDictionary::REPAIR,
            PartyRolesDictionary::INSURER, $insurer,
            PartyRolesDictionary::INSURED, $vehicle
        );
    }

    public function manufacturerWarrantyRegistered(PartyId $distributorId, PartyId $vehicleId): void
    {
        $distributor = $this->partyRepository->put($distributorId->toUuid());
        $vehicle = $this->partyRepository->put($vehicleId->toUuid());

        $this->partyRelationshipRepository->put(
            PartyRelationshipsDictionary::REPAIR,
            PartyRolesDictionary::GUARANTOR, $distributor,
            PartyRolesDictionary::CUSTOMER, $vehicle
        );
    }
}
