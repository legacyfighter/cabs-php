<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme;

use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\BaseState;

class DraftState extends BaseState
{
    //BAD IDEA!
    //public function publish(): self {
    //if some validation
    //    return new PublishedState();
    //}

    protected function canChangeContent(): bool
    {
        return true;
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
