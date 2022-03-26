<?php

namespace LegacyFighter\Cabs\Repair\Model\Dict;

use LegacyFighter\Cabs\Repair\Model\Roles\Blank\Customer;
use LegacyFighter\Cabs\Repair\Model\Roles\Blank\Insured;
use LegacyFighter\Cabs\Repair\Model\Roles\Repair\ExtendedInsurance;
use LegacyFighter\Cabs\Repair\Model\Roles\Repair\Warranty;

/**
 * Class that emulates database dictionary
 */
class PartyRolesDictionary
{
    public const INSURER = ExtendedInsurance::class;
    public const INSURED = Insured::class;
    public const GUARANTOR = Warranty::class;
    public const CUSTOMER = Customer::class;

    private function __construct()
    {
    }
}
