<?php

namespace LegacyFighter\Cabs\Contracts\Application\Acme\Straigthforward;

use LegacyFighter\Cabs\Contracts\Model\Content\DocumentNumber;

class ContractResult
{
    public const SUCCESS = 'success';
    public const ERROR = 'error';

    public function __construct(
        private string $result,
        private int $documentHeaderId,
        private DocumentNumber $documentNumber,
        private ?string $stateDescriptor
    )
    {
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getDocumentHeaderId(): int
    {
        return $this->documentHeaderId;
    }

    public function getDocumentNumber(): DocumentNumber
    {
        return $this->documentNumber;
    }

    public function getStateDescriptor(): ?string
    {
        return $this->stateDescriptor;
    }
}
