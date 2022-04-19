<?php

namespace LegacyFighter\Cabs\Agreements;

use LegacyFighter\Cabs\Entity\ContractAttachmentData;
use LegacyFighter\Cabs\Repository\ContractAttachmentDataRepository;

class ContractService
{

    public function __construct(
        private ContractRepository $contractRepository,
        private ContractAttachmentDataRepository $contractAttachmentDataRepository
    )
    {
    }

    public function createContract(ContractDTO $contractDTO): ContractDTO
    {
        $partnerContractsCount = count($this->contractRepository->findByPartnerName($contractDTO->getPartnerName())) + 1;
        return $this->findDto($this->contractRepository->save(new Contract(
            $contractDTO->getPartnerName(),
            $contractDTO->getSubject(),
            sprintf('C/%s/%s', $partnerContractsCount, $contractDTO->getPartnerName())
        ))->getId());
    }

    public function acceptContract(int $id): void
    {
        $this->find($id)->accept();
    }

    public function rejectContract(int $id): void
    {
        $this->find($id)->reject();
    }

    public function rejectAttachment(int $attachmentId): void
    {
        $this->contractRepository
            ->findByAttachmentId($attachmentId)
            ->rejectAttachment($this->contractRepository->findContractAttachmentNoById($attachmentId));
    }

    public function acceptAttachment(int $attachmentId): void
    {
        $this->contractRepository
            ->findByAttachmentId($attachmentId)
            ->acceptAttachment($this->contractRepository->findContractAttachmentNoById($attachmentId));
    }

    public function find(int $id): Contract
    {
        $contract = $this->contractRepository->getOne($id);
        if($contract===null) {
            throw new \InvalidArgumentException('Contract does not exist');
        }
        return $contract;
    }

    public function findDto(int $id): ContractDTO
    {
        return ContractDTO::from(
            $contract = $this->find($id),
            $this->contractAttachmentDataRepository->findByContractAttachmentNoIn($contract->getAttachmentIds())
        );
    }

    public function proposeAttachment(int $contractId, ContractAttachmentDTO $contractAttachmentDTO): ContractAttachmentDTO
    {
        $contract = $this->find($contractId);
        $contractAttachmentId = $contract->proposeAttachment()->getContractAttachmentNo();
        $this->contractRepository->save($contract);

        return ContractAttachmentDTO::from(
            $contract->findAttachment($contractAttachmentId),
            $this->contractAttachmentDataRepository->save(new ContractAttachmentData($contractAttachmentId, $contractAttachmentDTO->getData()))
        );
    }

    public function removeAttachment(int $contractId, int $attachmentId): void
    {
        //TODO sprawdzenie czy nalezy do kontraktu (JIRA: II-14455)
        $this->find($contractId)->remove($this->contractRepository->findContractAttachmentNoById($attachmentId));
        $this->contractAttachmentDataRepository->deleteByAttachmentId($attachmentId);
    }
}
