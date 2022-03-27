<?php

namespace LegacyFighter\Cabs\Contracts\Legacy;

use Doctrine\ORM\Mapping\Entity;

#[Entity]
class Document extends BaseAggregateRoot implements Printable
{
    private string $number;
    private ?string $title = null;
    private ?string $content = null;
    protected string $status = DocumentStatus::DRAFT;
    /**
     * @var User[]
     */
    private array $assignedUsers = [];
    private User $creator;
    private ?User $verifier;

    public function __construct(string $number, User $creator)
    {
        $this->number = $number;
        $this->creator = $creator;
    }

    public function verifyBy(User $verifier): void
    {
        if($this->status !== DocumentStatus::DRAFT) {
            throw new \RuntimeException(sprintf('Can not verify in status: %s', $this->status));
        }
        if($this->creator === $verifier) {
            throw new \RuntimeException('Verifier can not verify documents by himself');
        }
        $this->verifier = $verifier;
        $this->status = DocumentStatus::VERIFIED;
    }

    public function publish(): void
    {
        if($this->status !== DocumentStatus::VERIFIED) {
            throw new \RuntimeException(sprintf('Can not publish in status: %s', $this->status));
        }
        $this->status = DocumentStatus::PUBLISHED;
    }

    public function archive(): void
    {
        $this->status = DocumentStatus::ARCHIVED;
    }

    //===============================================================

    public function changeTitle(string $title): void
    {
        if($this->status === DocumentStatus::ARCHIVED || $this->status === DocumentStatus::PUBLISHED) {
            throw new \RuntimeException(sprintf('Can not change title in status: %s', $this->status));
        }
        $this->title = $title;
        if($this->status === DocumentStatus::VERIFIED) {
            $this->status = DocumentStatus::DRAFT;
        }
    }

    protected bool $overridePublished = false;

    public function changeContent(string $content): void
    {
        if($this->overridePublished) {
            $this->content = $content;
            return;
        }

        if($this->status === DocumentStatus::ARCHIVED || $this->status === DocumentStatus::PUBLISHED) {
            throw new \RuntimeException(sprintf('Can not change content in status: %s', $this->status));
        }
        $this->content = $content;
        if($this->status === DocumentStatus::VERIFIED) {
            $this->status = DocumentStatus::DRAFT;
        }
    }

    //===============================================================

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
