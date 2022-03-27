<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Straightforward;

use LegacyFighter\Cabs\Contracts\Model\ContentId;
use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;

abstract class BaseState
{
    protected ?DocumentHeader $documentHeader;

    public function init(DocumentHeader $documentHeader): void
    {
        $this->documentHeader = $documentHeader;
        $this->documentHeader->setStateDescriptor($this->getStateDescriptor());
    }

    public function getStateDescriptor(): string
    {
        return static::class;
    }

    public function getDocumentHeader(): ?DocumentHeader
    {
        return $this->documentHeader;
    }

    public function changeContent(ContentId $currentContent): self
    {
        if($this->canChangeContent()) {
            $newState = $this->stateAfterContentChange();
            $newState->init($this->documentHeader);
            $this->documentHeader->changeCurrentContent($currentContent);
            $newState->acquire($this->documentHeader);
            return $newState;
        }

        return $this;
    }

    public function changeState(self $newState): self
    {
        if($newState->canChangeFrom($this)) {
            $newState->init($this->documentHeader);
            $this->documentHeader->setStateDescriptor($newState->getStateDescriptor());
            $newState->acquire($this->documentHeader);
            return $newState;
        }
        return $this;
    }

    protected abstract function canChangeContent(): bool;
    protected abstract function stateAfterContentChange(): self;
    protected abstract function canChangeFrom(self $previousState): bool;

    /**
     * template method that allows to perform addition actions during state change
     */
    protected abstract function acquire(DocumentHeader $header): void;
}
