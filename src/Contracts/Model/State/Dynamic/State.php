<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic;

use LegacyFighter\Cabs\Contracts\Model\ContentId;
use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\ContentChange\NegativePredicate;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\StateChange\PositiveVerifier;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\StateChange\Predicate as StatePredicate;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\ContentChange\Predicate as ContentPredicate;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Actions\Action;
use Munus\Collection\Stream;

class State
{
    //before: getClass().getName()
    /**
     * Unique name of a state
     */
    private string $stateDescriptor;

    //TODO consider to get rid of this stateful object and transform State to reusable logic
    private DocumentHeader $documentHeader;

    //TODO consider merging contentChangePredicate and afterContentChangeState int one function

    //before: abstract canChangeContent()
    /**
     * predicates tested if content can be changed
     */
    private ContentPredicate $contentChangePredicate;

    //before: abstract stateAfterContentChange()
    /**
     * state after content change - may be the same as before content change
     */
    private ?State $afterContentChangeState = null;

    //before: abstract canChangeFrom(state)
    /**
     * possible transitions to other states with rules that need to be tested to determine if transition is legal
     * @var \SplObjectStorage<State,StatePredicate[]>
     */
    private \SplObjectStorage $stateChangePredicates;

    //before: abstract acquire()
    /**
     * actions that may be needed to perform while transition to the next state
     * @var Action[]
     */
    private array $afterStateChangeActions = [];

    public function __construct(string $stateDescriptor)
    {
        $this->stateDescriptor = $stateDescriptor;
        $this->contentChangePredicate = new NegativePredicate();
        $this->stateChangePredicates = new \SplObjectStorage();
        $this->addStateChangePredicates($this, [new PositiveVerifier()]); //change to self is always possible
    }

    public function init(DocumentHeader $documentHeader): void
    {
        $this->documentHeader = $documentHeader;
        $this->documentHeader->setStateDescriptor($this->getStateDescriptor());
    }

    public function changeContent(ContentId $contentId): State
    {
        if(!$this->isContentEditable()) {
            return $this;
        }

        $newState = $this->afterContentChangeState;//local variable just to focus attention
        if($newState->contentChangePredicate->test($this)) {
            $newState->init($this->documentHeader);
            $this->documentHeader->changeCurrentContent($contentId);
            return $newState;
        }

        return $this;
    }

    public function changeState(ChangeCommand $command): State
    {
        $desiredState = $this->find($command->getDesiredState());
        if($desiredState === null) {
            return $this;
        }

        $predicates = Stream::ofAll($this->stateChangePredicates[$desiredState] ?? []);
        if($predicates->filter(fn(StatePredicate $predicate) => $predicate->test($this, $command))->length() === $predicates->length()) {
            $desiredState->init($this->documentHeader);
            foreach ($desiredState->afterStateChangeActions as $action) {
                $action->apply($this->documentHeader, $command);
            }
            return $desiredState;
        }

        return $this;
    }

    /**
     * @param StatePredicate[] $predicatesToAdd
     */
    public function addStateChangePredicates(State $toState, array $predicatesToAdd): void
    {
        if(isset($this->stateChangePredicates[$toState])) {
            $this->stateChangePredicates[$toState] = array_merge($this->stateChangePredicates[$toState], $predicatesToAdd);
        } else {
            $this->stateChangePredicates[$toState] = $predicatesToAdd;
        }
    }

    public function addAfterStateChangeAction(Action $action): void
    {
        $this->afterStateChangeActions[] = $action;
    }

    public function isContentEditable(): bool
    {
        return $this->afterContentChangeState !== null;
    }

    public function getDocumentHeader(): DocumentHeader
    {
        return $this->documentHeader;
    }

    public function getStateDescriptor(): string
    {
        return $this->stateDescriptor;
    }

    public function getContentChangePredicate(): ContentPredicate
    {
        return $this->contentChangePredicate;
    }

    public function getStateChangePredicates(): \SplObjectStorage
    {
        return $this->stateChangePredicates;
    }

    public function setAfterContentChangeState(?State $afterContentChangeState): void
    {
        $this->afterContentChangeState = $afterContentChangeState;
    }

    public function setContentChangePredicate(ContentPredicate $contentChangePredicate): void
    {
        $this->contentChangePredicate = $contentChangePredicate;
    }

    private function find(string $desiredState): ?State
    {
        /** @var State $state */
        foreach ($this->stateChangePredicates as $state) {
            if($state->getStateDescriptor() === $desiredState) {
                return $state;
            }
        }

        return null;
    }
}
