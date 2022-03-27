<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme;

use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\BaseState;

class PublishedState extends BaseState
{
    protected function canChangeContent(): bool
    {
        return false;
    }

    protected function stateAfterContentChange(): BaseState
    {
        return $this;
    }

    protected function canChangeFrom(BaseState $previousState): bool
    {
        return $previousState instanceof VerifiedState && $previousState->getDocumentHeader()->noEmpty();
    }

    protected function acquire(DocumentHeader $header): void
    {

    }


}
