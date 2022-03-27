<?php

namespace LegacyFighter\Cabs\Tests\Integration\Contracts\Application\Acme\Dynamic;

use LegacyFighter\Cabs\Contracts\Application\Acme\Dynamic\DocumentResourceManager;
use LegacyFighter\Cabs\Contracts\Application\Editor\CommitResult;
use LegacyFighter\Cabs\Contracts\Application\Editor\DocumentDTO;
use LegacyFighter\Cabs\Contracts\Application\Editor\DocumentEditor;
use LegacyFighter\Cabs\Contracts\Legacy\User;
use LegacyFighter\Cabs\Contracts\Legacy\UserRepository;
use LegacyFighter\Cabs\Contracts\Model\Content\ContentVersion;
use LegacyFighter\Cabs\Contracts\Model\Content\DocumentNumber;
use LegacyFighter\Cabs\Contracts\Model\ContentId;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Acme\AcmeContractStateAssembler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AcmeContractManagerBasedOnDynamicStateModelTest extends KernelTestCase
{
    private DocumentEditor $editor;
    private DocumentResourceManager $documentResourceManager;
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
        $this->documentResourceManager = $this->getContainer()->get(DocumentResourceManager::class);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
        $this->anyVersion = new ContentVersion('v1');
        $this->author = $this->userRepository->save(new User());
        $this->verifier = $this->userRepository->save(new User());
    }

    /**
     * @test
     */
    public function authorCanNotVerify(): void
    {
        //given
        $this->createAcmeContract($this->author);
        $contentId = $this->commitContent(self::CONTENT_1);
        $result = $this->documentResourceManager->changeContent($this->headerId, $contentId);
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::DRAFT);
        //when
        $result = $this->documentResourceManager->changeState($this->headerId, AcmeContractStateAssembler::VERIFIED, $this->verifierParam());
        //then
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::VERIFIED)->editable()->possibleNextStates(AcmeContractStateAssembler::PUBLISHED, AcmeContractStateAssembler::ARCHIVED);
    }

    /**
     * @test
     */
    public function verifierOtherThanAuthorCanVerify(): void
    {
        //given
        $this->createAcmeContract($this->author);
        $contentId = $this->commitContent(self::CONTENT_1);
        $result = $this->documentResourceManager->changeContent($this->headerId, $contentId);
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::DRAFT)->editable()->possibleNextStates(AcmeContractStateAssembler::VERIFIED, AcmeContractStateAssembler::ARCHIVED);
        //when
        $result = $this->documentResourceManager->changeState($this->headerId, AcmeContractStateAssembler::VERIFIED, $this->authorParam());
        //then
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::DRAFT);
    }

    /**
     * @test
     */
    public function changingContentOfVerifiedMovesBackToDraft(): void
    {
        //given
        $this->createAcmeContract($this->author);
        $contentId = $this->commitContent(self::CONTENT_1);
        $result = $this->documentResourceManager->changeContent($this->headerId, $contentId);
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::DRAFT)->editable();

        $result = $this->documentResourceManager->changeState($this->headerId, AcmeContractStateAssembler::VERIFIED, $this->verifierParam());
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::VERIFIED)->editable();
        //when
        $contentId = $this->commitContent(self::CONTENT_2);
        $result = $this->documentResourceManager->changeContent($this->headerId, $contentId);
        //then
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::DRAFT)->editable();
    }

    /**
     * @test
     */
    public function publishedCanNotBeChanged(): void
    {
        //given
        $this->createAcmeContract($this->author);
        $firstContentId = $contentId = $this->commitContent(self::CONTENT_1);
        $result = $this->documentResourceManager->changeContent($this->headerId, $contentId);
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::DRAFT)->editable();

        $result = $this->documentResourceManager->changeState($this->headerId, AcmeContractStateAssembler::VERIFIED, $this->verifierParam());
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::VERIFIED)->editable();

        $result = $this->documentResourceManager->changeState($this->headerId, AcmeContractStateAssembler::PUBLISHED, []);
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::PUBLISHED)->uneditable();
        //when
        $newContentId = $this->commitContent(self::CONTENT_2);
        $result = $this->documentResourceManager->changeContent($this->headerId, $newContentId);
        //then
        (new DocumentOperationResultAssert($result))->state(AcmeContractStateAssembler::PUBLISHED)->uneditable()->content($firstContentId);
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
        $result = $this->documentResourceManager->createDocument($user->getId());
        $this->documentNumber = $result->getDocumentNumber();
        $this->headerId = $result->getDocumentHeaderId();
    }

    private function verifierParam(): array
    {
        return [
            AcmeContractStateAssembler::PARAM_VERIFIER => $this->verifier->getId()
        ];
    }

    private function authorParam(): array
    {
        return [
            AcmeContractStateAssembler::PARAM_VERIFIER => $this->author->getId()
        ];
    }
}
