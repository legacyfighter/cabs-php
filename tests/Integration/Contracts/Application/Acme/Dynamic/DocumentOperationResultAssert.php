<?php

namespace LegacyFighter\Cabs\Tests\Integration\Contracts\Application\Acme\Dynamic;

use LegacyFighter\Cabs\Contracts\Application\Acme\Dynamic\DocumentOperationResult;
use LegacyFighter\Cabs\Contracts\Model\ContentId;
use PHPUnit\Framework\Assert;

class DocumentOperationResultAssert extends Assert
{
    private DocumentOperationResult $result;

    public function __construct(DocumentOperationResult $result)
    {
        $this->result = $result;
        self::assertEquals(DocumentOperationResult::SUCCESS, $result->getResult());
    }

    public function editable(): self
    {
        self::assertTrue($this->result->isContentChangePossible());
        return $this;
    }

    public function uneditable(): self
    {
        self::assertFalse($this->result->isContentChangePossible());
        return $this;
    }

    public function state(string $state): self
    {
        self::assertEquals($state, $this->result->getStateName());
        return $this;
    }

    public function content(ContentId $contentId): self
    {
        self::assertEquals($contentId, $this->result->getContentId());
        return $this;
    }

    public function possibleNextStates(string ...$states): self
    {
        self::assertEquals($states, array_keys($this->result->getPossibleTransitionsAndRules()));
        return $this;
    }
}
