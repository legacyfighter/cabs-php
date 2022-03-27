<?php

namespace LegacyFighter\Cabs\Tests\Unit\Contracts\Legacy;

use LegacyFighter\Cabs\Contracts\Legacy\Document;
use LegacyFighter\Cabs\Contracts\Legacy\DocumentStatus;
use LegacyFighter\Cabs\Contracts\Legacy\User;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    private string $anyNumber = 'number';
    private string $title = 'title';
    private User $anyUser;
    private User $otherUser;

    protected function setUp(): void
    {
        $this->anyUser = new User();
        $this->otherUser = new User();
    }

    /**
     * @test
     */
    public function onlyDraftCanBeVerifiedByUserOtherThanCreator(): void
    {
        $doc = new Document($this->anyNumber, $this->anyUser);

        $doc->verifyBy($this->otherUser);

        self::assertEquals(DocumentStatus::VERIFIED, $doc->getStatus());
    }

    /**
     * @test
     */
    public function canNotChangePublished(): void
    {
        $doc = new Document($this->anyNumber, $this->anyUser);
        $doc->changeTitle($this->title);
        $doc->verifyBy($this->otherUser);
        $doc->publish();

        try {
            $doc->changeTitle('');
        } catch (\Throwable $exception) {
            self::assertTrue(true);
        }

        self::assertEquals($this->title, $doc->getTitle());
    }

    /**
     * @test
     */
    public function changingVerifiedMovesToDraft(): void
    {
        $doc = new Document($this->anyNumber, $this->anyUser);
        $doc->changeTitle($this->title);
        $doc->verifyBy($this->otherUser);

        $doc->changeTitle('');

        self::assertEquals(DocumentStatus::DRAFT, $doc->getStatus());
    }
}
