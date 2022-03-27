<?php

namespace LegacyFighter\Cabs\Contracts\Model\State\Dynamic;

class ChangeCommand
{
    private string $desiredState;
    /**
     * @var array<string,mixed>
     */
    private array $params;

    public function __construct(string $desiredState, array $params = [])
    {
        $this->desiredState = $desiredState;
        $this->params = $params;
    }

    public function getDesiredState(): string
    {
        return $this->desiredState;
    }

    public function getParam(string $name)
    {
        return $this->params[$name] ?? null;
    }
}
