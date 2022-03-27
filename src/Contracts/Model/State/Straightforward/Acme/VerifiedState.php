<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme;

use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\BaseState;

class VerifiedState extends BaseState
{
    private ?int $verifierId;

    public function __construct(?int $verifierId = null)
    {
        $this->verifierId = $verifierId;
    }

    protected function canChangeContent(): bool
    {
        return true;
    }

    protected function stateAfterContentChange(): BaseState
    {
        return new DraftState();
    }

    protected function canChangeFrom(BaseState $previousState): bool
    {
        return $previousState instanceof DraftState
            && $previousState->getDocumentHeader()->getAuthorId() !== $this->verifierId
            && $previousState->getDocumentHeader()->noEmpty()
        ;
    }

    protected function acquire(DocumentHeader $header): void
    {
        $this->documentHeader->setVerifierId($this->verifierId);
    }


}
