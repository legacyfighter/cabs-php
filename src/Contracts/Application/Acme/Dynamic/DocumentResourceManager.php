<?php

namespace LegacyFighter\Cabs\Contracts\Application\Acme\Dynamic;

use LegacyFighter\Cabs\Contracts\Legacy\UserRepository;
use LegacyFighter\Cabs\Contracts\Model\Content\DocumentNumber;
use LegacyFighter\Cabs\Contracts\Model\ContentId;
use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\DocumentHeaderRepository;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\Acme\AcmeContractStateAssembler;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\ChangeCommand;
use LegacyFighter\Cabs\Contracts\Model\State\Dynamic\State;

class DocumentResourceManager
{
    public function __construct(
        private DocumentHeaderRepository $documentHeaderRepository,
        private AcmeContractStateAssembler $assembler,
        private UserRepository $userRepository
    ) {}

    public function createDocument(int $authorId): DocumentOperationResult
    {
        $author = $this->userRepository->getOne($authorId);

        $number = $this->generateNumber();
        $documentHeader = new DocumentHeader($author->getId(), $number);

        $stateConfig = $this->assembler->assemble();
        $state = $stateConfig->begin($documentHeader);

        $this->documentHeaderRepository->save($documentHeader);

        return $this->generateDocumentOperationResult(DocumentOperationResult::SUCCESS, $state);
    }

    public function changeState(int $documentId, string $desiredState, array $params): DocumentOperationResult
    {
        $documentHeader = $this->documentHeaderRepository->getOne($documentId);
        $stateConfig = $this->assembler->assemble();
        $state = $stateConfig->recreate($documentHeader);

        $state = $state->changeState(new ChangeCommand($desiredState, $params));

        $this->documentHeaderRepository->save($documentHeader);

        return $this->generateDocumentOperationResult(DocumentOperationResult::SUCCESS, $state);
    }

    public function changeContent(int $headerId, ContentId $contentVersion): DocumentOperationResult
    {
        $documentHeader = $this->documentHeaderRepository->getOne($headerId);
        $stateConfig = $this->assembler->assemble();
        $state = $stateConfig->recreate($documentHeader);

        $state = $state->changeContent($contentVersion);

        $this->documentHeaderRepository->save($documentHeader);

        return $this->generateDocumentOperationResult(DocumentOperationResult::SUCCESS, $state);
    }

    private function generateDocumentOperationResult(string $result, State $state): DocumentOperationResult
    {
        return new DocumentOperationResult(
            $result,
            $state->getStateDescriptor(),
            $state->getDocumentHeader()->getContentId(),
            $state->getDocumentHeader()->getId(),
            $state->getDocumentHeader()->getDocumentNumber(),
            $this->extractPossibleTransitionsAndRules($state),
            $state->isContentEditable(),
            $this->extractContentChangePredicate($state)
        );
    }

    private function extractContentChangePredicate(State $state): ?string
    {
        if($state->isContentEditable()) {
            (new \ReflectionClass($state->getContentChangePredicate()))->getShortName();
        }

        return null;
    }

    /**
     * @return array<string,array<string>>
     */
    private function extractPossibleTransitionsAndRules(State $state): array
    {
        $transitionsAndRules = [];
        $stateChangePredicates = $state->getStateChangePredicates();
        /** @var State $s */
        foreach ($state->getStateChangePredicates() as $s) {
            //transition to self is not important
            if($s == $state) {
                continue;
            }
            foreach ($stateChangePredicates[$s] as $predicate) {
                $transitionsAndRules[$s->getStateDescriptor()][] = (new \ReflectionClass($predicate))->getShortName();
            }
        }
        return $transitionsAndRules;
    }

    private function generateNumber(): DocumentNumber
    {
        return new DocumentNumber('nr: '.random_int(1, PHP_INT_MAX)); //TODO integrate with doc number generator
    }
}
