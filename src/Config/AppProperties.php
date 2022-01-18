<?php

namespace LegacyFighter\Cabs\Config;

final class AppProperties
{
    private int $noOfTransitsForClaimAutomaticRefund = 5;
    private int $automaticRefundForVipThreshold = 2;
    private int $minNoOfCarsForEcoClass = 3;

    private int $milesExpirationInDays = 365;
    private int $defaultMilesBonus = 10;

    public function getNoOfTransitsForClaimAutomaticRefund(): int
    {
        return $this->noOfTransitsForClaimAutomaticRefund;
    }

    public function getAutomaticRefundForVipThreshold(): int
    {
        return $this->automaticRefundForVipThreshold;
    }

    public function getMinNoOfCarsForEcoClass(): int
    {
        return $this->minNoOfCarsForEcoClass;
    }

    public function getMilesExpirationInDays(): int
    {
        return $this->milesExpirationInDays;
    }

    public function getDefaultMilesBonus(): int
    {
        return $this->defaultMilesBonus;
    }
}
