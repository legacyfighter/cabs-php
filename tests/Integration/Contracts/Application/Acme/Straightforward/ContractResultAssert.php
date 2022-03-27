<?php

namespace LegacyFighter\Cabs\Tests\Integration\Contracts\Application\Acme\Straightforward;

use LegacyFighter\Cabs\Contracts\Application\Acme\Straigthforward\ContractResult;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\BaseState;
use PHPUnit\Framework\Assert;

class ContractResultAssert extends Assert
{
    private ContractResult $result;

    public function __construct(ContractResult $result)
    {
        $this->result = $result;
        self::assertEquals(ContractResult::SUCCESS, $result->getResult());
    }

    public function state(BaseState $state): self
    {
        self::assertEquals($state->getStateDescriptor(), $this->result->getStateDescriptor());
        return $this;
    }
}
