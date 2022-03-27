<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme;

use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\BaseState;

class ArchivedState extends BaseState
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
        return true;
    }

    protected function acquire(DocumentHeader $header): void
    {

    }

}
