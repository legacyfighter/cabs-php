<?php

namespace LegacyFighter\Cabs\Tests\Unit\Contracts\Model\State\Dynamic;

use LegacyFighter\Cabs\Contracts\Model\Content\DocumentNumber;
use LegacyFighter\Cabs\Contracts\Model\ContentId;
use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Acme\AcmeContractStateAssembler;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\ChangeCommand;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Config\Events\DocumentPublished;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\State;
use LegacyFighter\Cabs\Tests\Common\PrivateProperty;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class AcmeContractTest extends TestCase
{
    private DocumentNumber $anyNumber;
    private int $anyUser = 1;
    private int $otherUser = 2;
    private ContentId $anyVersion;
    private ContentId $otherVersion;
    private FakeDocumentPublisher $publisher;

    protected function setUp(): void
    {
        $this->anyNumber = new DocumentNumber('nr: 1');
        $this->anyVersion = new ContentId(Uuid::v4());
        $this->otherVersion = new ContentId(Uuid::v4());
        $this->publisher = new FakeDocumentPublisher();
    }

    /**
     * @test
     */
    public function canNotChangePublished(): void
    {
        //given
        $state = $this->draft()->changeContent($this->anyVersion)
            ->changeState(new ChangeCommand(AcmeContractStateAssembler::VERIFIED, [AcmeContractStateAssembler::PARAM_VERIFIER => $this->otherUser]))
            ->changeState(new ChangeCommand(AcmeContractStateAssembler::PUBLISHED))
        ;
        $this->publisher->contains(DocumentPublished::class);
        $this->publisher->reset();
        //when
        $state = $state->changeContent($this->otherVersion);
        //then
        $this->publisher->noEvents();
        self::assertEquals(AcmeContractStateAssembler::PUBLISHED, $state->getStateDescriptor());
        self::assertEquals($this->anyVersion, $state->getDocumentHeader()->getContentId());
    }

    /**
     * @test
     */
    public function changingVerifiedMovesToDraft(): void
    {
        //given
        $state = $this->draft()->changeContent($this->anyVersion)
            ->changeState(new ChangeCommand(AcmeContractStateAssembler::VERIFIED, [AcmeContractStateAssembler::PARAM_VERIFIER => $this->otherUser]))
        ;
        //when
        $state = $state->changeContent($this->otherVersion);
        //then
        self::assertEquals(AcmeContractStateAssembler::DRAFT, $state->getStateDescriptor());
        self::assertEquals($this->otherVersion, $state->getDocumentHeader()->getContentId());
    }

    /**
     * @test
     */
    public function canChangeStateToTheSame(): void
    {
        $state = $this->draft()->changeContent($this->anyVersion);
        self::assertEquals(AcmeContractStateAssembler::DRAFT, $state->getStateDescriptor());
        $state->changeState(new ChangeCommand(AcmeContractStateAssembler::DRAFT));
        self::assertEquals(AcmeContractStateAssembler::DRAFT, $state->getStateDescriptor());

        $state = $state->changeState(new ChangeCommand(AcmeContractStateAssembler::VERIFIED, [AcmeContractStateAssembler::PARAM_VERIFIER => $this->otherUser]));
        self::assertEquals(AcmeContractStateAssembler::VERIFIED, $state->getStateDescriptor());
        $state = $state->changeState(new ChangeCommand(AcmeContractStateAssembler::VERIFIED, [AcmeContractStateAssembler::PARAM_VERIFIER => $this->otherUser]));
        self::assertEquals(AcmeContractStateAssembler::VERIFIED, $state->getStateDescriptor());

        $state = $state->changeState(new ChangeCommand(AcmeContractStateAssembler::PUBLISHED));
        self::assertEquals(AcmeContractStateAssembler::PUBLISHED, $state->getStateDescriptor());
        $state = $state->changeState(new ChangeCommand(AcmeContractStateAssembler::PUBLISHED));
        self::assertEquals(AcmeContractStateAssembler::PUBLISHED, $state->getStateDescriptor());

        $state = $state->changeState(new ChangeCommand(AcmeContractStateAssembler::ARCHIVED));
        self::assertEquals(AcmeContractStateAssembler::ARCHIVED, $state->getStateDescriptor());
        $state = $state->changeState(new ChangeCommand(AcmeContractStateAssembler::ARCHIVED));
        self::assertEquals(AcmeContractStateAssembler::ARCHIVED, $state->getStateDescriptor());
    }

    /**
     * @test
     */
    public function draftCanBeVerifiedByUserOtherThanCreator(): void
    {
        //given
        $state = $this->draft()->changeContent($this->anyVersion);
        //when
        $state = $state->changeState(new ChangeCommand(AcmeContractStateAssembler::VERIFIED, [AcmeContractStateAssembler::PARAM_VERIFIER => $this->otherUser]));
        //then
        self::assertEquals(AcmeContractStateAssembler::VERIFIED, $state->getStateDescriptor());
        self::assertEquals($this->otherUser, $state->getDocumentHeader()->getVerifierId());
    }

    private function draft(): State
    {
        $header = new DocumentHeader($this->anyUser, $this->anyNumber);
        PrivateProperty::setId(1, $header);
        $header->setStateDescriptor(AcmeContractStateAssembler::DRAFT);
        $assembler = new AcmeContractStateAssembler($this->publisher);
        $config = $assembler->assemble();

        return $config->recreate($header);
    }
}
