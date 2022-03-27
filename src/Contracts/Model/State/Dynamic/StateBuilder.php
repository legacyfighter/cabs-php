<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic;

use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Actions\Action;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\ContentChange\PositivePredicate;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\StateChange\Predicate;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Predicates\StateChange\PreviousStateVerifier;

class StateBuilder implements StateConfig
{
    // Rules for state transition check method called or from method called
    public const MODE_STATE_CHANGE = 'state-change';

    //Rules for content change {@link #whenContentChanged() whenContentChanged}  method called
    public const MODE_CONTENT_CHANGE = 'content-change';

    private ?string $mode = null;

    /**
     * @var array<string,State>
     */
    private array $states = []; //all states configured so far

    //below is the current state of the builder, gathered whit assembling methods, current state is reset in to() method
    private ?State $fromState = null;
    private ?State $initialState = null;

    /**
     * @var Predicate[]
     */
    private array $predicates = [];

    //========= methods for application layer - business process

    public function begin(DocumentHeader $header): State
    {
        $header->setStateDescriptor($this->initialState->getStateDescriptor());
        return $this->recreate($header);
    }

    public function recreate(DocumentHeader $header): State
    {
        $state = $this->states[$header->getStateDescriptor()];
        $state->init($header);
        return $state;
    }

    //======= methods for assembling process

    /**
     * Similar to the from method, but marks initial state
     */
    public function beginWith(string $stateName): self
    {
        if($this->initialState !== null) {
            throw new \RuntimeException(sprintf('Initial state already set to: %S', $this->initialState->getStateDescriptor()));
        }

        $config = $this->from($stateName);
        $this->initialState = $this->fromState;
        return $config;
    }

    /**
     * Begins a rule sequence with a beginning state
     */
    public function from(string $stateName): self
    {
        $this->mode = self::MODE_STATE_CHANGE;
        $this->predicates = [];
        $this->fromState = $this->getOrPut($stateName);
        return $this;
    }

    /**
     * Adds a rule to the current sequence
     */
    public function check(Predicate $predicate): self
    {
        $this->mode = self::MODE_STATE_CHANGE;
        $this->predicates[] = $predicate;
        return $this;
    }

    /**
     * Ends a rule sequence with a destination state
     */
    public function to(string $stateName): FinalStateConfig
    {
        $toState = $this->getOrPut($stateName);
        switch ($this->mode) {
            case self::MODE_STATE_CHANGE:
                $this->predicates[] = new PreviousStateVerifier($this->fromState->getStateDescriptor());
                $this->fromState->addStateChangePredicates($toState, $this->predicates);
                break;
            case self::MODE_CONTENT_CHANGE:
                $this->fromState->setAfterContentChangeState($toState);
                $toState->setContentChangePredicate(new PositivePredicate());
        }

        $this->predicates = [];
        $this->fromState = null;
        $this->mode = null;

        return new FinalStateConfig($toState);
    }

    /**
     * Adds a rule of state change after a content change
     */
    public function whenContentChanged(): self
    {
        $this->mode = self::MODE_CONTENT_CHANGE;
        return $this;
    }

    private function getOrPut(string $stateName): State
    {
        if(!isset($this->states[$stateName])) {
            $this->states[$stateName] = new State($stateName);
        }

        return $this->states[$stateName];
    }
}

class FinalStateConfig
{
    public function __construct(private State $state) {}

    public function action(Action $action): self
    {
        $this->state->addAfterStateChangeAction($action);
        return $this;
    }
}
