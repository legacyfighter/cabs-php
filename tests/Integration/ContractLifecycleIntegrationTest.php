<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\DTO\ContractAttachmentDTO;
use LegacyFighter\Cabs\DTO\ContractDTO;
use LegacyFighter\Cabs\Entity\Contract;
use LegacyFighter\Cabs\Entity\ContractAttachment;
use LegacyFighter\Cabs\Service\ContractService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ContractLifecycleIntegrationTest extends KernelTestCase
{
    private ContractService $contractService;

    protected function setUp(): void
    {
        $this->contractService = $this->getContainer()->get(ContractService::class);
    }

    /**
     * @test
     */
    public function canCreateContract(): void
    {
        //given
        $contract = $this->createContract('partnerNameVeryUnique', 'umowa o cenę');

        //when
        $loaded = $this->loadContract($contract->getId());

        //then
        self::assertEquals('partnerNameVeryUnique', $loaded->getPartnerName());
        self::assertEquals('umowa o cenę', $loaded->getSubject());
        self::assertEquals('C/1/partnerNameVeryUnique', $loaded->getContractNo());
        self::assertEquals(Contract::STATUS_NEGOTIATIONS_IN_PROGRESS, $loaded->getStatus());
        self::assertNull($loaded->getChangeDate());
        self::assertNull($loaded->getAcceptedAt());
        self::assertNull($loaded->getRejectedAt());
    }

    /**
     * @test
     */
    public function secondContractForTheSamePartnerHasCorrectNo(): void
    {
        //given
        $first = $this->createContract('uniqueName', 'umowa o cenę');

        //when
        $second = $this->createContract('uniqueName', 'umowa o cenę');
        //then
        $firstLoaded = $this->loadContract($first->getId());
        $secondLoaded = $this->loadContract($second->getId());

        self::assertEquals('uniqueName', $firstLoaded->getPartnerName());
        self::assertEquals('uniqueName', $secondLoaded->getPartnerName());
        self::assertEquals('C/1/uniqueName', $firstLoaded->getContractNo());
        self::assertEquals('C/2/uniqueName', $secondLoaded->getContractNo());
    }

    /**
     * @test
     */
    public function canAddAttachmentToContract(): void
    {
        //given
        $contract = $this->createContract('partnerNameVeryUnique', 'umowa o cenę');

        //when
        $this->addAttachmentToContract($contract, 'content');

        //then
        $loaded = $this->loadContract($contract->getId());
        self::assertCount(1, $loaded->getAttachments());
        self::assertEquals('content', $loaded->getAttachments()[0]->getData());
        self::assertEquals(ContractAttachment::STATUS_PROPOSED, $loaded->getAttachments()[0]->getStatus());
    }

    /**
     * @test
     */
    public function canRemoveAttachmentFromContract(): void
    {
        //given
        $contract = $this->createContract('partnerNameVeryUnique', 'umowa o cenę');
        //and
        $attachment = $this->addAttachmentToContract($contract, 'content');

        //when
        $this->removeAttachmentFromContract($contract, $attachment);

        //then
        $loaded = $this->loadContract($contract->getId());
        self::assertCount(0, $loaded->getAttachments());
    }

    /**
     * @test
     */
    public function canAcceptAttachmentByOneSide(): void
    {
        //given
        $contract = $this->createContract('partnerNameVeryUnique', 'umowa o cenę');
        //and
        $attachment = $this->addAttachmentToContract($contract, 'content');

        //when
        $this->acceptAttachment($attachment);

        //then
        $loaded = $this->loadContract($contract->getId());
        self::assertCount(1, $loaded->getAttachments());
        self::assertEquals(ContractAttachment::STATUS_ACCEPTED_BY_ONE_SIDE, $loaded->getAttachments()[0]->getStatus());
    }

    /**
     * @test
     */
    public function canAcceptAttachmentByTwoSides(): void
    {
        //given
        $contract = $this->createContract('partnerName', 'umowa o cenę');
        //and
        $attachment = $this->addAttachmentToContract($contract, 'content');

        //when
        $this->acceptAttachment($attachment);
        //and
        $this->acceptAttachment($attachment);

        //then
        $loaded = $this->loadContract($contract->getId());
        self::assertCount(1, $loaded->getAttachments());
        self::assertEquals(ContractAttachment::STATUS_ACCEPTED_BY_BOTH_SIDES, $loaded->getAttachments()[0]->getStatus());
    }

    /**
     * @test
     */
    public function canRejectAttachment(): void
    {
        //given
        $contract = $this->createContract('partnerNameVeryUnique', 'umowa o cenę');
        //and
        $attachment = $this->addAttachmentToContract($contract, 'content');

        //when
        $this->rejectAttachment($attachment);

        //then
        $loaded = $this->loadContract($contract->getId());
        self::assertCount(1, $loaded->getAttachments());
        self::assertEquals(ContractAttachment::STATUS_REJECTED, $loaded->getAttachments()[0]->getStatus());
    }

    /**
     * @test
     */
    public function canAcceptContractWhenAllAttachmentsAccepted(): void
    {
        //given
        $contract = $this->createContract('partnerName', 'umowa o cenę');
        //and
        $attachment = $this->addAttachmentToContract($contract, 'content');
        //and
        $this->acceptAttachment($attachment);
        $this->acceptAttachment($attachment);

        //when
        $this->acceptContract($contract);

        //then
        $loaded = $this->loadContract($contract->getId());
        self::assertEquals(Contract::STATUS_ACCEPTED, $loaded->getStatus());
    }

    /**
     * @test
     */
    public function canRejectContract(): void
    {
        //given
        $contract = $this->createContract('partnerName', 'umowa o cenę');
        //and
        $attachment = $this->addAttachmentToContract($contract, 'content');
        //and
        $this->acceptAttachment($attachment);
        $this->acceptAttachment($attachment);

        //when
        $this->rejectContract($contract);

        //then
        $loaded = $this->loadContract($contract->getId());
        self::assertEquals(Contract::STATUS_REJECTED, $loaded->getStatus());
    }

    /**
     * @test
     */
    public function cannotAcceptContractWhenNotAllAttachmentsAccepted(): void
    {
        //given
        $contract = $this->createContract('partnerName', 'umowa o cenę');
        //and
        $attachment = $this->addAttachmentToContract($contract, 'content');
        //and
        $this->acceptAttachment($attachment);

        //then
        $this->expectException(\RuntimeException::class);

        //when
        $this->acceptContract($contract);
    }

    private function loadContract(int $id): ContractDTO
    {
        return $this->contractService->findDto($id);
    }

    private function createContract(string $partnerName, string $subject): ContractDTO
    {
        $dto = ContractDTO::with($partnerName, $subject);
        $contract = $this->contractService->createContract($dto);
        return $this->loadContract($contract->getId());
    }

    private function addAttachmentToContract(ContractDTO $created, string $content): ContractAttachmentDTO
    {
        $contractAttachment = ContractAttachmentDTO::with($content);
        return $this->contractService->proposeAttachment($created->getId(), $contractAttachment);
    }

    private function removeAttachmentFromContract(ContractDTO $contract, ContractAttachmentDTO $attachment): void
    {
        $this->contractService->removeAttachment($contract->getId(), $attachment->getId());
    }

    private function acceptAttachment(ContractAttachmentDTO $attachment): void
    {
        $this->contractService->acceptAttachment($attachment->getId());
    }

    private function rejectAttachment(ContractAttachmentDTO $attachment): void
    {
        $this->contractService->rejectAttachment($attachment->getId());
    }

    private function acceptContract(ContractDTO $contract): void
    {
        $this->contractService->acceptContract($contract->getId());
    }

    private function rejectContract(ContractDTO $contract): void
    {
        $this->contractService->rejectContract($contract->getId());
    }
}
