<?php

namespace LegacyFighter\Cabs\Contracts\Application\Acme\Straigthforward;

use LegacyFighter\Cabs\Contracts\Legacy\UserRepository;
use LegacyFighter\Cabs\Contracts\Model\Content\DocumentNumber;
use LegacyFighter\Cabs\Contracts\Model\ContentId;
use LegacyFighter\Cabs\Contracts\Model\DocumentHeader;
use LegacyFighter\Cabs\Contracts\Model\DocumentHeaderRepository;
use LegacyFighter\Cabs\Contracts\Model\State\Straightforward\Acme\VerifiedState;

class AcmeContractProcessBasedOnStraightforwardDocumentModel
{
    public function __construct(
        private UserRepository $userRepository,
        private DocumentHeaderRepository $documentHeaderRepository,
        private AcmeStateFactory $stateFactory
    )
    {
    }

    public function createContract(int $authorId): ContractResult
    {
        $author = $this->userRepository->getOne($authorId);

        $number = $this->generateNumber();
        $header = new DocumentHeader($author->getId(), $number);

        $this->documentHeaderRepository->save($header);

        return new ContractResult(ContractResult::SUCCESS, $header->getId(), $number, $header->getStateDescriptor());
    }

    public function verify(int $headerId, int $verifierId): ContractResult
    {
        $verifier = $this->userRepository->getOne($verifierId);
        //TODO user authorization

        $header = $this->documentHeaderRepository->getOne($headerId);

        $state = $this->stateFactory->create($header);
        $state = $state->changeState(new VerifiedState($verifier->getId()));

        $this->documentHeaderRepository->save($header);

        return new ContractResult(ContractResult::SUCCESS, $header->getId(), $header->getDocumentNumber(), $header->getStateDescriptor());
    }

    public function changeContent(int $headerId, ContentId $contentVersion): ContractResult
    {
        $header = $this->documentHeaderRepository->getOne($headerId);

        $state = $this->stateFactory->create($header);
        $state = $state->changeContent($contentVersion);

        $this->documentHeaderRepository->save($header);

        return new ContractResult(ContractResult::SUCCESS, $header->getId(), $header->getDocumentNumber(), $header->getStateDescriptor());
    }

    private function generateNumber(): DocumentNumber
    {
        return new DocumentNumber('nr: '.random_int(1, PHP_INT_MAX)); //TODO integrate with doc number generator
    }
}
