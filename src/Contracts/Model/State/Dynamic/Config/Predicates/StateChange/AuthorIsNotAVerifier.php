<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\StateChange;

use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\ChangeCommand;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Actions\ChangeVerifier;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\State;

class AuthorIsNotAVerifier implements Predicate
{
    public function test(State $state, ChangeCommand $command): bool
    {
        return $command->getParam(ChangeVerifier::PARAM_VERIFIER) !== $state->getDocumentHeader()->getAuthorId();
    }
}
