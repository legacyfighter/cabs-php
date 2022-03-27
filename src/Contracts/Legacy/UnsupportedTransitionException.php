<?php

namespace LegacyFighter\Cabs\Contracts\Legacy;

class UnsupportedTransitionException extends \RuntimeException
{
    public function __construct(
        private string $current,
        private string $desired
    )
    {
        parent::__construct(sprintf('can not transit form %s to %s', $current, $desired));
    }

    public function getCurrent(): string
    {
        return $this->current;
    }

    public function getDesired(): string
    {
        return $this->desired;
    }
}
