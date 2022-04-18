<?php

namespace LegacyFighter\Cabs\Tests\Double;

use LegacyFighter\Cabs\Config\AppProperties;

class FakeAppProperties extends AppProperties
{
    private int $automaticRefundForVipThreshold = 2;
    private int $noOfTransitsForClaimAutomaticRefund = 5;

    private int $milesExpirationInDays = 365;
    private int $defaultMilesBonus = 0;

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

    public function getMilesExpirationInDays(): int
    {
        return $this->milesExpirationInDays;
    }

    public function setMilesExpirationInDays(int $milesExpirationInDays): void
    {
        $this->milesExpirationInDays = $milesExpirationInDays;
    }

    public function getDefaultMilesBonus(): int
    {
        return $this->defaultMilesBonus;
    }

    public function setDefaultMilesBonus(int $defaultMilesBonus): void
    {
        $this->defaultMilesBonus = $defaultMilesBonus;
    }
}
