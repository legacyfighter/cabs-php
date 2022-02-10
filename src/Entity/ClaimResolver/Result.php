<?php

namespace LegacyFighter\Cabs\Entity\ClaimResolver;

use LegacyFighter\Cabs\Entity\Claim;

class Result
{
    public const ASK_DRIVER = 'ask-driver';
    public const ASK_CLIENT = 'ask-client';
    public const ASK_NOONE = 'ask-noone';

    private string $whoToAsk;
    private string $decision;

    public function __construct(string $whoToAsk, string $decision)
    {
        if(!in_array($whoToAsk, [self::ASK_DRIVER, self::ASK_CLIENT, self::ASK_NOONE], true)) {
            throw new \InvalidArgumentException();
        }
        if(!in_array($decision, Claim::ALL_STATUSES, true)) {
            throw new \InvalidArgumentException();
        }

        $this->whoToAsk = $whoToAsk;
        $this->decision = $decision;
    }

    public function getWhoToAsk(): string
    {
        return $this->whoToAsk;
    }

    public function getDecision(): string
    {
        return $this->decision;
    }
}
