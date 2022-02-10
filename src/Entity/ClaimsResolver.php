<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Entity\ClaimResolver\Result;

#[Entity]
class ClaimsResolver extends BaseEntity
{
    #[Column(type: 'integer')]
    private int $clientId;

    /**
     * @var int[]
     */
    #[Column(type: 'json')]
    private array $claimedTransitsIds = [];

    public function __construct(int $clientId)
    {
        $this->clientId = $clientId;
    }

    public function resolve(
        Claim $claim,
        int $automaticRefundForVipThreshold,
        int $numberOfTransits,
        int $noOfTransitsForClaimAutomaticRefund
    ): Result
    {
        $transitId = $claim->getTransit()->getId();
        if(in_array($transitId, $this->claimedTransitsIds, true)) {
            return new Result(Result::ASK_NOONE, Claim::STATUS_ESCALATED);
        }
        $this->claimedTransitsIds[] = $transitId;
        if(count($this->claimedTransitsIds) <= 3) {
            return new Result(Result::ASK_NOONE, Claim::STATUS_REFUNDED);
        }
        if($claim->getOwner()->getType() === Client::TYPE_VIP) {
            if($claim->getTransit()->getPrice()->toInt() < $automaticRefundForVipThreshold) {
                return new Result(Result::ASK_NOONE, Claim::STATUS_REFUNDED);
            } else {
                return new Result(Result::ASK_DRIVER, Claim::STATUS_ESCALATED);
            }
        } else {
            if($numberOfTransits > $noOfTransitsForClaimAutomaticRefund) {
                if($claim->getTransit()->getPrice()->toInt() < $automaticRefundForVipThreshold) {
                    return new Result(Result::ASK_NOONE, Claim::STATUS_REFUNDED);
                } else {
                    return new Result(Result::ASK_CLIENT, Claim::STATUS_ESCALATED);
                }
            } else {
                return new Result(Result::ASK_DRIVER, Claim::STATUS_ESCALATED);
            }
        }
    }
}
