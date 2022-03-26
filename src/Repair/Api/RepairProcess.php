<?php

namespace LegacyFighter\Cabs\Repair\Api;

use LegacyFighter\Cabs\Party\Api\PartyMapper;
use LegacyFighter\Cabs\Party\Api\RoleObjectFactory;
use LegacyFighter\Cabs\Party\Model\Party\PartyRelationship;
use LegacyFighter\Cabs\Repair\Model\Dict\PartyRelationshipsDictionary;
use LegacyFighter\Cabs\Repair\Model\Roles\Repair\RepairingResult;
use LegacyFighter\Cabs\Repair\Model\Roles\Repair\RoleForRepairer;
use Munus\Control\Option;
use Munus\Control\Option\None;

class RepairProcess
{
    public function __construct(
        private PartyMapper $partyMapper
    )
    {
    }

    public function resolve(RepairRequest $repairRequest): ResolveResult
    {
        return $this->partyMapper->mapRelation($repairRequest->getVehicle(), PartyRelationshipsDictionary::REPAIR)
            ->map(fn(PartyRelationship $r) => RoleObjectFactory::from($r))
            ->map(fn(RoleObjectFactory $rof) => $rof->getRole(RoleForRepairer::class)->getOrElse(Option::none()))
            ->map(fn(RoleForRepairer $role) => $role->handle($repairRequest))
            ->map(fn(RepairingResult $result) => new ResolveResult(ResolveResult::STATUS_SUCCESS, $result->getHandlingParty(), $result->getTotalCost(), $result->getHandledParts()))
            ->getOrElse(new ResolveResult(ResolveResult::STATUS_ERROR));
    }

    public function resolve_oldschool_version(RepairRequest $repairRequest): ResolveResult
    {
        //who is responsible for repairing the vehicle
        $relationship = $this->partyMapper->mapRelation($repairRequest->getVehicle(), PartyRelationshipsDictionary::REPAIR);
        if($relationship->isPresent()) {
            $roleObjectFactory = RoleObjectFactory::from($relationship->get());
            //dynamically assigned rules
            $role = $roleObjectFactory->getRole(RoleForRepairer::class);
            if($role->isPresent()) {
                //actual repair request handling
                $result = $role->get()->handle($repairRequest);
                return new ResolveResult(ResolveResult::STATUS_SUCCESS, $result->getHandlingParty(), $result->getTotalCost(), $result->getHandledParts());
            }
        }
        return new ResolveResult(ResolveResult::STATUS_ERROR);
    }
}
