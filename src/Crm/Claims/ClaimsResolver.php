<?php

namespace LegacyFighter\Cabs\Crm\Claims;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use LegacyFighter\Cabs\Common\BaseEntity;
use LegacyFighter\Cabs\Crm\Claims\ClaimResolver\Result;
use LegacyFighter\Cabs\Entity\Client;

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
        string $clientType,
        int $automaticRefundForVipThreshold,
        int $numberOfTransits,
        int $noOfTransitsForClaimAutomaticRefund
    ): Result
    {
        $transitId = $claim->getTransitId();
        if(in_array($transitId, $this->claimedTransitsIds, true)) {
            return new Result(Result::ASK_NOONE, Claim::STATUS_ESCALATED);
        }
        $this->claimedTransitsIds[] = $transitId;
        if(count($this->claimedTransitsIds) <= 3) {
            return new Result(Result::ASK_NOONE, Claim::STATUS_REFUNDED);
        }
        if($clientType === Client::TYPE_VIP) {
            if($claim->getTransitPrice()->toInt() < $automaticRefundForVipThreshold) {
                return new Result(Result::ASK_NOONE, Claim::STATUS_REFUNDED);
            } else {
                return new Result(Result::ASK_DRIVER, Claim::STATUS_ESCALATED);
            }
        } else {
            if($numberOfTransits > $noOfTransitsForClaimAutomaticRefund) {
                if($claim->getTransitPrice()->toInt() < $automaticRefundForVipThreshold) {
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
