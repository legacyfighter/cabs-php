<?php

namespace LegacyFighter\Cabs\Tests\Double;

use LegacyFighter\Cabs\Config\AppProperties;

class FakeAppProperties extends AppProperties
{
    private int $automaticRefundForVipThreshold = 2;
    private int $noOfTransitsForClaimAutomaticRefund = 5;

    public function getAutomaticRefundForVipThreshold(): int
    {
        return $this->automaticRefundForVipThreshold;
    }

    public function setAutomaticRefundForVipThreshold(int $automaticRefundForVipThreshold): void
    {
        $this->automaticRefundForVipThreshold = $automaticRefundForVipThreshold;
    }

    public function getNoOfTransitsForClaimAutomaticRefund(): int
    {
        return $this->noOfTransitsForClaimAutomaticRefund;
    }

    public function setNoOfTransitsForClaimAutomaticRefund(int $noOfTransitsForClaimAutomaticRefund): void
    {
        $this->noOfTransitsForClaimAutomaticRefund = $noOfTransitsForClaimAutomaticRefund;
    }
}
