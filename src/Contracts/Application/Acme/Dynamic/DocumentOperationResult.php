<?php

namespace LegacyFighter\Cabs\Contracts\Application\Acme\Dynamic;

use LegacyFighter\Cabs\Contracts\Model\Content\DocumentNumber;
use LegacyFighter\Cabs\Contracts\Model\ContentId;

class DocumentOperationResult
{
    public const SUCCESS = 'success';
    public const ERROR = 'error';

    public function __construct(
        private string $result,
        private string $stateName,
        private ?ContentId $contentId,
        private int $documentHeaderId,
        private DocumentNumber $documentNumber,
        private array $possibleTransitionsAndRules,
        private bool $contentChangePossible,
        private ?string $contentChangePredicate
    )
    {
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getStateName(): string
    {
        return $this->stateName;
    }

    public function getContentId(): ?ContentId
    {
        return $this->contentId;
    }

    public function getDocumentHeaderId(): int
    {
        return $this->documentHeaderId;
    }

    public function getDocumentNumber(): DocumentNumber
    {
        return $this->documentNumber;
    }

    public function getPossibleTransitionsAndRules(): array
    {
        return $this->possibleTransitionsAndRules;
    }

    public function isContentChangePossible(): bool
    {
        return $this->contentChangePossible;
    }

    public function getContentChangePredicate(): string
    {
        return $this->contentChangePredicate;
    }
}
