<?php

namespace LegacyFighter\Cabs\Tests\Unit\Entity;

use LegacyFighter\Cabs\Agreements\Contract;
use LegacyFighter\Cabs\Agreements\ContractAttachment;
use PHPUnit\Framework\TestCase;

class ContractLifecycleTest extends TestCase
{
    /**
     * @test
     */
    public function canCreateContract(): void
    {
        //when
        $contract = $this->createContract("partnerNameVeryUnique", "umowa o cenę");

        //then
        self::assertEquals('partnerNameVeryUnique', $contract->getPartnerName());
        self::assertEquals('umowa o cenę', $contract->getSubject());
        self::assertEquals(Contract::STATUS_NEGOTIATIONS_IN_PROGRESS, $contract->getStatus());
        self::assertNull($contract->getChangeDate());
        self::assertNull($contract->getAcceptedAt());
        self::assertNull($contract->getRejectedAt());
    }

    /**
     * @test
     */
    public function canAddAttachmentToContract(): void
    {
        //given
        $contract = $this->createContract("partnerNameVeryUnique", "umowa o cenę");

        //when
        $contractAttachment = $contract->proposeAttachment();

        //then
        self::assertCount(1, $contract->getAttachments());
        self::assertEquals(ContractAttachment::STATUS_PROPOSED, $contract->findAttachment($contractAttachment->getContractAttachmentNo())->getStatus());
    }

    /**
     * @test
     */
    public function canRemoveAttachmentFromContract(): void
    {
        //given
        $contract = $this->createContract("partnerNameVeryUnique", "umowa o cenę");
        //and
        $attachment = $contract->proposeAttachment();

        //when
        $contract->remove($attachment->getContractAttachmentNo());

        //then
        self::assertCount(0, $contract->getAttachments());
    }

    /**
     * @test
     */
    public function canAcceptAttachmentByOneSide(): void
    {
        //given
        $contract = $this->createContract("partnerNameVeryUnique", "umowa o cenę");
        //and
        $attachment = $contract->proposeAttachment();

        //when
        $contract->acceptAttachment($attachment->getContractAttachmentNo());

        //then
        self::assertCount(1, $contract->getAttachments());
        self::assertEquals(ContractAttachment::STATUS_ACCEPTED_BY_ONE_SIDE, $contract->findAttachment($attachment->getContractAttachmentNo())->getStatus());
    }

    /**
     * @test
     */
    public function canAcceptAttachmentByTwoSides(): void
    {
        //given
        $contract = $this->createContract("partnerNameVeryUnique", "umowa o cenę");
        //and
        $attachment = $contract->proposeAttachment();

        //when
        $contract->acceptAttachment($attachment->getContractAttachmentNo());
        //and
        $contract->acceptAttachment($attachment->getContractAttachmentNo());

        //then
        self::assertCount(1, $contract->getAttachments());
        self::assertEquals(ContractAttachment::STATUS_ACCEPTED_BY_BOTH_SIDES, $contract->findAttachment($attachment->getContractAttachmentNo())->getStatus());
    }

    /**
     * @test
     */
    public function canRejectAttachment(): void
    {
        //given
        $contract = $this->createContract("partnerNameVeryUnique", "umowa o cenę");
        //and
        $attachment = $contract->proposeAttachment();

        //when
        $contract->rejectAttachment($attachment->getContractAttachmentNo());

        //then
        self::assertCount(1, $contract->getAttachments());
        self::assertEquals(ContractAttachment::STATUS_REJECTED, $contract->findAttachment($attachment->getContractAttachmentNo())->getStatus());
    }

    /**
     * @test
     */
    public function canAcceptContractWhenAllAttachmentsAccepted(): void
    {
        //given
        $contract = $this->createContract("partnerNameVeryUnique", "umowa o cenę");
        //and
        $attachment = $contract->proposeAttachment();
        //and
        $contract->acceptAttachment($attachment->getContractAttachmentNo());
        $contract->acceptAttachment($attachment->getContractAttachmentNo());

        //when
        $contract->accept();

        //then
        self::assertEquals(Contract::STATUS_ACCEPTED, $contract->getStatus());
    }

    /**
     * @test
     */
    public function canRejectContract(): void
    {
        //given
        $contract = $this->createContract("partnerNameVeryUnique", "umowa o cenę");
        //and
        $attachment = $contract->proposeAttachment();
        //and
        $contract->acceptAttachment($attachment->getContractAttachmentNo());
        $contract->acceptAttachment($attachment->getContractAttachmentNo());

        //when
        $contract->reject();

        //then
        self::assertEquals(Contract::STATUS_REJECTED, $contract->getStatus());
    }

    /**
     * @test
     */
    public function cannotAcceptContractWhenNotAllAttachmentsAccepted(): void
    {
        //given
        $contract = $this->createContract("partnerNameVeryUnique", "umowa o cenę");
        //and
        $attachment = $contract->proposeAttachment();
        //and
        $contract->acceptAttachment($attachment->getContractAttachmentNo());

        //then
        $this->expectException(\RuntimeException::class);

        //when
        $contract->accept();
    }

    private function createContract(string $partnerName, string $subject): Contract
    {
        return new Contract($partnerName, $subject, 'no');
    }
}
