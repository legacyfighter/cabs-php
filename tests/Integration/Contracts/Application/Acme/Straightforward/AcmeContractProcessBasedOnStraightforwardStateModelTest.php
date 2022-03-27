<?php

namespace LegacyFighter\Cabs\Tests\Integration\Contracts\Application\Acme\Straightforward;

use LegacyFighter\Cabs\Contracts\Application\Acme\Straigthforward\AcmeContractProcessBasedOnStraightforwardDocumentModel;
use LegacyFighter\Cabs\Contracts\Application\Editor\CommitResult;
use LegacyFighter\Cabs\Contracts\Application\Editor\DocumentDTO;
use LegacyFighter\Cabs\Contracts\Application\Editor\DocumentEditor;
use LegacyFighter\Cabs\Contracts\Legacy\User;
use LegacyFighter\Cabs\Contracts\Legacy\UserRepository;
use LegacyFighter\Cabs\Contracts\Model\Content\ContentVersion;
use LegacyFighter\Cabs\Contracts\Model\Content\DocumentNumber;
use LegacyFighter\Cabs\Contracts\Model\ContentId;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme\DraftState;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme\VerifiedState;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AcmeContractProcessBasedOnStraightforwardStateModelTest extends KernelTestCase
{
    private DocumentEditor $editor;
    private AcmeContractProcessBasedOnStraightforwardDocumentModel $contractProcess;
    private UserRepository $userRepository;

    private const CONTENT_1 = 'content 1';
    private const CONTENT_2 = 'content 2';
    private ContentVersion $anyVersion;
    private User $author;
    private User $verifier;
    private DocumentNumber $documentNumber;
    private int $headerId;

    protected function setUp(): void
    {
        $this->editor = $this->getContainer()->get(DocumentEditor::class);
        $this->contractProcess = $this->getContainer()->get(AcmeContractProcessBasedOnStraightforwardDocumentModel::class);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
        $this->anyVersion = new ContentVersion('v1');
        $this->author = $this->userRepository->save(new User());
        $this->verifier = $this->userRepository->save(new User());
    }

    /**
     * @test
     */
    public function verifierOtherThanAuthorCanVerify(): void
    {
        //given
        $this->createAcmeContract($this->author);
        $contentId = $this->commitContent(self::CONTENT_1);
        $this->contractProcess->changeContent($this->headerId, $contentId);
        //when
        $result = $this->contractProcess->verify($this->headerId, $this->verifier->getId());
        //then
        (new ContractResultAssert($result))->state(new VerifiedState($this->verifier->getId()));
    }

    /**
     * @test
     */
    public function authorCanNotVerify(): void
    {
        //given
        $this->createAcmeContract($this->author);
        $contentId = $this->commitContent(self::CONTENT_1);
        $this->contractProcess->changeContent($this->headerId, $contentId);
        //when
        $result = $this->contractProcess->verify($this->headerId, $this->author->getId());
        //then
        (new ContractResultAssert($result))->state(new DraftState());
    }

    /**
     * @test
     */
    public function changingContentOfVerifiedMovesBackToDraft(): void
    {
        //given
        $this->createAcmeContract($this->author);
        $contentId = $this->commitContent(self::CONTENT_1);
        $result = $this->contractProcess->changeContent($this->headerId, $contentId);
        (new ContractResultAssert($result))->state(new DraftState());

        $result = $this->contractProcess->verify($this->headerId, $this->verifier->getId());
        (new ContractResultAssert($result))->state(new VerifiedState($this->verifier->getId()));
        //when
        $contentId = $this->commitContent(self::CONTENT_2);
        //then
        $result = $this->contractProcess->changeContent($this->headerId, $contentId);
        (new ContractResultAssert($result))->state(new DraftState());
    }

    private function commitContent(string $content): ContentId
    {
        $doc = new DocumentDTO(null, $content, $this->anyVersion);
        $result = $this->editor->commit($doc);
        self::assertEquals(CommitResult::SUCCESS, $result->getResult());
        return new ContentId($result->getContentId());
    }

    private function createAcmeContract(User $user): void
    {
        $contractResult = $this->contractProcess->createContract($user->getId());
        $this->documentNumber = $contractResult->getDocumentNumber();
        $this->headerId = $contractResult->getDocumentHeaderId();
    }
}
