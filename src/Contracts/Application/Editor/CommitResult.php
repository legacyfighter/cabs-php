<?php

namespace LegacyFighter\Cabs\Contracts\Application\Editor;

use Symfony\Component\Uid\Uuid;

class CommitResult
{
    public const FAILURE = 'failure';
    public const SUCCESS = 'success';

    private Uuid $contentId;
    private string $result;
    private ?string $message;

    public function __construct(Uuid $contentId, string $result, ?string $message = null)
    {
        $this->contentId = $contentId;
        $this->result = $result;
        $this->message = $message;
    }

    public function getContentId(): Uuid
    {
        return $this->contentId;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
