<?php

namespace LegacyFighter\Cabs\Service;

use LegacyFighter\Cabs\DTO\ContractAttachmentDTO;
use LegacyFighter\Cabs\DTO\ContractDTO;
use LegacyFighter\Cabs\Entity\Contract;
use LegacyFighter\Cabs\Entity\ContractAttachment;
use LegacyFighter\Cabs\Repository\ContractAttachmentRepository;
use LegacyFighter\Cabs\Repository\ContractRepository;

class ContractService
{
    private ContractRepository $contractRepository;
    private ContractAttachmentRepository $contractAttachmentRepository;

    public function __construct(ContractRepository $contractRepository, ContractAttachmentRepository $contractAttachmentRepository)
    {
        $this->contractRepository = $contractRepository;
        $this->contractAttachmentRepository = $contractAttachmentRepository;
    }

    public function createContract(ContractDTO $contractDTO): Contract
    {
        $partnerContractsCount = count($this->contractRepository->findByPartnerName($contractDTO->getPartnerName())) + 1;
        return $this->contractRepository->save(new Contract(
            $contractDTO->getPartnerName(),
            $contractDTO->getSubject(),
            sprintf('C/%s/%s', $partnerContractsCount, $contractDTO->getPartnerName())
        ));
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
        $this->contractRepository->findByAttachmentId($attachmentId)->rejectAttachment($attachmentId);
    }

    public function acceptAttachment(int $attachmentId): void
    {
        $this->contractRepository->findByAttachmentId($attachmentId)->acceptAttachment($attachmentId);
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
        return ContractDTO::from($this->find($id), $this->contractAttachmentRepository->findByContractId($id));
    }

    public function proposeAttachment(int $contractId, ContractAttachmentDTO $contractAttachmentDTO): ContractAttachmentDTO
    {
        $contract = $this->find($contractId);
        return ContractAttachmentDTO::from($this->contractAttachmentRepository->save($contract->proposeAttachment($contractAttachmentDTO->getData())));
    }

    public function removeAttachment(int $contractId, int $attachmentId): void
    {
        //TODO sprawdzenie czy nalezy do kontraktu (JIRA: II-14455)
        $this->contractAttachmentRepository->deleteById($attachmentId);
    }
}
