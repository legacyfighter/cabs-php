<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\StateChange;

use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\ChangeCommand;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\State;

class PreviousStateVerifier implements Predicate
{
    public function __construct(
        private string $stateDescriptor
    ){}

    public function test(State $state, ChangeCommand $command): bool
    {
        return $state->getStateDescriptor() === $this->stateDescriptor;
    }
}
