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
        $contract = new Contract();
        $contract->setPartnerName($contractDTO->getPartnerName());
        $partnerContractsCount = count($this->contractRepository->findByPartnerName($contractDTO->getPartnerName())) + 1;
        $contract->setSubject($contractDTO->getSubject());
        $contract->setContractNo(sprintf('C/%s/%s', $partnerContractsCount, $contractDTO->getPartnerName()));
        return $this->contractRepository->save($contract);
    }

    public function acceptContract(int $id): void
    {
        $contract = $this->find($id);
        $attachments = $this->contractAttachmentRepository->findByContract($contract);
        if(count(array_filter($attachments, fn(ContractAttachment $attachment) => $attachment->getStatus() === ContractAttachment::STATUS_ACCEPTED_BY_BOTH_SIDES)) === count($attachments)) {
            $contract->setStatus(Contract::STATUS_ACCEPTED);
        } else {
            throw new \RuntimeException('Not all attachments accepted by both sides');
        }
    }

    public function rejectContract(int $id): void
    {
        $contract = $this->find($id);
        $contract->setStatus(Contract::STATUS_REJECTED);
    }

    public function rejectAttachment(int $attachmentId): void
    {
        $contractAttachment = $this->contractAttachmentRepository->getOne($attachmentId);
        $contractAttachment->setStatus(ContractAttachment::STATUS_REJECTED);
    }

    public function acceptAttachment(int $attachmentId): void
    {
        $contractAttachment = $this->contractAttachmentRepository->getOne($attachmentId);
        if($contractAttachment->getStatus() === ContractAttachment::STATUS_ACCEPTED_BY_ONE_SIDE || $contractAttachment->getStatus() === ContractAttachment::STATUS_ACCEPTED_BY_BOTH_SIDES) {
            $contractAttachment->setStatus(ContractAttachment::STATUS_ACCEPTED_BY_BOTH_SIDES);
        } else {
            $contractAttachment->setStatus(ContractAttachment::STATUS_ACCEPTED_BY_ONE_SIDE);
        }
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
        return ContractDTO::from($this->find($id));
    }

    public function proposeAttachment(int $contractId, ContractAttachmentDTO $contractAttachmentDTO): ContractAttachmentDTO
    {
        $contract = $this->find($contractId);
        $contractAttachment = new ContractAttachment();
        $contractAttachment->setContract($contract);
        $contractAttachment->setData($contractAttachmentDTO->getData());
        $this->contractAttachmentRepository->save($contractAttachment);
        $attachments = $contract->getAttachments();
        $attachments[] = $contractAttachment;
        $contract->setAttachments($attachments);
        return ContractAttachmentDTO::from($contractAttachment);
    }

    public function removeAttachment(int $contractId, int $attachmentId): void
    {
        //TODO sprawdzenie czy nalezy do kontraktu (JIRA: II-14455)
        $this->contractAttachmentRepository->deleteById($attachmentId);
    }
}
