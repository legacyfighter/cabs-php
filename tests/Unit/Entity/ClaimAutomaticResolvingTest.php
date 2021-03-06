<?php

namespace LegacyFighter\Cabs\Tests\Unit\Entity;

use LegacyFighter\Cabs\Crm\Claims\Claim;
use LegacyFighter\Cabs\Crm\Claims\ClaimResolver\Result;
use LegacyFighter\Cabs\Crm\Claims\ClaimsResolver;
use LegacyFighter\Cabs\Crm\Client;
use LegacyFighter\Cabs\Money\Money;
use LegacyFighter\Cabs\Ride\Transit;
use LegacyFighter\Cabs\Tests\Common\Factory;
use LegacyFighter\Cabs\Tests\Common\PrivateProperty;
use PHPUnit\Framework\TestCase;

class ClaimAutomaticResolvingTest extends TestCase
{
    /**
     * @test
     */
    public function secondClaimForTheSameTransitWillBeEscalated(): void
    {
        //given
        $resolver = new ClaimsResolver(1);
        //and
        $transit = $this->aTransit(1);
        //and
        $claim = $this->createClaim($transit, 39);
        //and
        $resolver->resolve($claim, Client::TYPE_NORMAL, 40, 15, 10);
        //and
        $claim2 = $this->createClaim($transit, 39);

        //when
        $result = $resolver->resolve($claim2, Client::TYPE_NORMAL, 40, 15, 10);

        //then
        self::assertEquals(Claim::STATUS_ESCALATED, $result->getDecision());
        self::assertEquals(Result::ASK_NOONE, $result->getWhoToAsk());
    }

    /**
     * @test
     */
    public function lowCostTransitsAreRefundedIfClientIsVIP(): void
    {
        //given
        $resolver = new ClaimsResolver(1);
        //and
        $transit = $this->aTransit(1);
        //and
        $claim = $this->createClaim($transit, 39);

        //when
        $result = $resolver->resolve($claim, Client::TYPE_VIP, 40, 15, 10);

        //then
        self::assertEquals(Claim::STATUS_REFUNDED, $result->getDecision());
        self::assertEquals(Result::ASK_NOONE, $result->getWhoToAsk());
    }

    /**
     * @test
     */
    public function highCostTransitsAreEscalatedEvenWhenClientIsVIP(): void
    {
        //given
        $resolver = new ClaimsResolver(1);
        //and
        $claim = $this->createClaim($this->aTransit(1), 39);
        $resolver->resolve($claim, Client::TYPE_VIP, 40, 15, 10);
        $claim2 = $this->createClaim($this->aTransit(2), 39);
        $resolver->resolve($claim2, Client::TYPE_VIP, 40, 15, 10);
        $claim3 = $this->createClaim($this->aTransit(3), 39);
        $resolver->resolve($claim3, Client::TYPE_VIP, 40, 15, 10);
        //and
        $claim4 = $this->createClaim($this->aTransit(4), 41);

        //when
        $result = $resolver->resolve($claim4, Client::TYPE_VIP, 40, 15, 10);

        //then
        self::assertEquals(Claim::STATUS_ESCALATED, $result->getDecision());
        self::assertEquals(Result::ASK_DRIVER, $result->getWhoToAsk());
    }

    /**
     * @test
     */
    public function firstThreeClaimsAreRefunded(): void
    {
        //given
        $resolver = new ClaimsResolver(1);
        //and
        $claim = $this->createClaim($this->aTransit(1), 39);
        $result1 = $resolver->resolve($claim, Client::TYPE_NORMAL, 40, 15, 10);
        $claim2 = $this->createClaim($this->aTransit(2), 39);
        $result2 = $resolver->resolve($claim2, Client::TYPE_NORMAL, 40, 15, 10);
        $claim3 = $this->createClaim($this->aTransit(3), 39);
        $result3 = $resolver->resolve($claim3, Client::TYPE_NORMAL, 40, 15, 10);

        //when
        $claim4 = $this->createClaim($this->aTransit(4), 39);
        $result4 = $resolver->resolve($claim4, Client::TYPE_NORMAL, 40, 4, 10);

        //then
        self::assertEquals(Claim::STATUS_REFUNDED, $result1->getDecision());
        self::assertEquals(Claim::STATUS_REFUNDED, $result2->getDecision());
        self::assertEquals(Claim::STATUS_REFUNDED, $result3->getDecision());
        self::assertEquals(Claim::STATUS_ESCALATED, $result4->getDecision());

        self::assertEquals(Result::ASK_NOONE, $result1->getWhoToAsk());
        self::assertEquals(Result::ASK_NOONE, $result2->getWhoToAsk());
        self::assertEquals(Result::ASK_NOONE, $result3->getWhoToAsk());
    }

    /**
     * @test
     */
    public function lowCostTransitsAreRefundedWhenManyTransits(): void
    {
        //given
        $resolver = new ClaimsResolver(1);
        //and
        $claim = $this->createClaim($this->aTransit(1), 39);
        $resolver->resolve($claim, Client::TYPE_NORMAL, 40, 15, 10);
        $claim2 = $this->createClaim($this->aTransit(2), 39);
        $resolver->resolve($claim2, Client::TYPE_NORMAL, 40, 15, 10);
        $claim3 = $this->createClaim($this->aTransit(3), 39);
        $resolver->resolve($claim3, Client::TYPE_NORMAL, 40, 15, 10);
        //and
        $claim4 = $this->createClaim($this->aTransit(4), 39);

        //when
        $result = $resolver->resolve($claim4, Client::TYPE_NORMAL, 40, 10, 9);

        //then
        self::assertEquals(Claim::STATUS_REFUNDED, $result->getDecision());
        self::assertEquals(Result::ASK_NOONE, $result->getWhoToAsk());
    }

    /**
     * @test
     */
    public function highCostTransitsAreEscalatedEvenWithManyTransits(): void
    {
        //given
        $resolver = new ClaimsResolver(1);
        //and
        $claim = $this->createClaim($this->aTransit(1), 39);
        $resolver->resolve($claim, Client::TYPE_NORMAL, 40, 15, 10);
        $claim2 = $this->createClaim($this->aTransit(2), 39);
        $resolver->resolve($claim2, Client::TYPE_NORMAL, 40, 15, 10);
        $claim3 = $this->createClaim($this->aTransit(3), 39);
        $resolver->resolve($claim3, Client::TYPE_NORMAL, 40, 15, 10);
        //and
        $claim4 = $this->createClaim($this->aTransit(4), 50);

        //when
        $result = $resolver->resolve($claim4, Client::TYPE_NORMAL, 40, 12, 10);

        //then
        self::assertEquals(Claim::STATUS_ESCALATED, $result->getDecision());
        self::assertEquals(Result::ASK_CLIENT, $result->getWhoToAsk());
    }

    /**
     * @test
     */
    public function highCostTransitsAreEscalatedWhenFewTransits(): void
    {
        //given
        $resolver = new ClaimsResolver(1);
        //and
        $claim = $this->createClaim($this->aTransit(1), 39);
        $resolver->resolve($claim, Client::TYPE_NORMAL, 40, 15, 10);
        $claim2 = $this->createClaim($this->aTransit(2), 39);
        $resolver->resolve($claim2, Client::TYPE_NORMAL, 40, 15, 10);
        $claim3 = $this->createClaim($this->aTransit(3), 39);
        $resolver->resolve($claim3, Client::TYPE_NORMAL, 40, 15, 10);
        //and
        $claim4 = $this->createClaim($this->aTransit(4), 50);

        //when
        $result = $resolver->resolve($claim4, Client::TYPE_NORMAL, 40, 2, 10);

        //then
        self::assertEquals(Claim::STATUS_ESCALATED, $result->getDecision());
        self::assertEquals(Result::ASK_DRIVER, $result->getWhoToAsk());
    }

    private function aTransit(int $id): Transit
    {
        return Factory::transit($id);
    }

    private function createClaim(Transit $transit, int $transitPrice): Claim
    {
        $claim = new Claim();
        $claim->setTransitId($transit->getId());
        $claim->setTransitPrice(Money::from($transitPrice));
        $claim->setOwnerId(1);
        return $claim;
    }
}
