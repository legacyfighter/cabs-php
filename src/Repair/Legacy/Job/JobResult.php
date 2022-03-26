<?php

namespace LegacyFighter\Cabs\Repair\Legacy\Job;

class JobResult
{
    public const DECISION_REDIRECTION = 'redirection';
    public const DECISION_ACCEPTED = 'accepted';
    public const DECISION_ERROR = 'error';

    private string $decision;

    /**
     * @var array<string, mixed>
     */
    private array $params = [];

    public function __construct(string $decision)
    {
        if(!in_array($decision, [self::DECISION_ACCEPTED, self::DECISION_ERROR, self::DECISION_REDIRECTION])) {
            throw new \InvalidArgumentException();
        }

        $this->decision = $decision;
    }

    public function addParam(string $name, $value): self
    {
        $this->params[$name] = $value;

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getParam(string $name)
    {
        return $this->params[$name] ?? null;
    }

    public function getDecision(): string
    {
        return $this->decision;
    }
}
