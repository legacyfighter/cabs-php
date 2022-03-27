<?php

namespace LegacyFighter\Cabs\Tests\Unit\Contracts\Model\State\Straightforward;

use LegacyFighter\Cabs\Contracts\Model\Content\DocumentNumber;
use LegacyFighter\Cabs\Contracts\Model\ContentId;
use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme\DraftState;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme\PublishedState;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme\VerifiedState;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\BaseState;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class AcmeContractTest extends TestCase
{
    private DocumentNumber $anyNumber;
    private int $anyUser = 1;
    private int $otherUser = 2;
    private ContentId $anyVersion;
    private ContentId $otherVersion;

    protected function setUp(): void
    {
        $this->anyNumber = new DocumentNumber('nr: 1');
        $this->anyVersion = new ContentId(Uuid::v4());
        $this->otherVersion = new ContentId(Uuid::v4());
    }

    /**
     * @test
     */
    public function onlyDraftCanBeVerifiedByUserOtherThanCreator(): void
    {
        //given
        $state = $this->draft()->changeContent($this->anyVersion);
        //when
        $state = $state->changeState(new VerifiedState($this->otherUser));
        //then
        self::assertInstanceOf(VerifiedState::class, $state);
        self::assertEquals($this->otherUser, $state->getDocumentHeader()->getVerifierId());
    }

    /**
     * @test
     */
    public function canNotChangePublished(): void
    {
        //given
        $state = $this->draft()->changeContent($this->anyVersion)->changeState(new VerifiedState($this->otherUser))->changeState(new PublishedState());
        //when
        $state = $state->changeContent($this->otherVersion);
        //then
        self::assertInstanceOf(PublishedState::class, $state);
        self::assertEquals($this->anyVersion, $state->getDocumentHeader()->getContentId());
    }

    /**
     * @test
     */
    public function changingVerifiedMovesToDraft(): void
    {
        //given
        $state = $this->draft()->changeContent($this->anyVersion);
        //when
        $state = $state->changeState(new VerifiedState($this->otherUser))->changeContent($this->otherVersion);
        //then
        self::assertInstanceOf(DraftState::class, $state);
        self::assertEquals($this->otherUser, $state->getDocumentHeader()->getVerifierId());
    }

    private function draft(): BaseState
    {
        $header = new DocumentHeader($this->anyUser, $this->anyNumber);
        $state = new DraftState();
        $state->init($header);

        return $state;
    }
}
