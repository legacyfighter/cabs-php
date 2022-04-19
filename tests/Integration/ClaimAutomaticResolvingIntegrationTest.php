<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Crm\Claims\Claim;
use LegacyFighter\Cabs\Crm\Claims\ClaimDTO;
use LegacyFighter\Cabs\Crm\Claims\ClaimNumberGenerator;
use LegacyFighter\Cabs\Crm\Claims\ClaimRepository;
use LegacyFighter\Cabs\Crm\Claims\ClaimService;
use LegacyFighter\Cabs\Crm\Claims\ClaimsResolverRepository;
use LegacyFighter\Cabs\DriverFleet\Driver;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Loyalty\AwardsService;
use LegacyFighter\Cabs\Notification\ClientNotificationService;
use LegacyFighter\Cabs\Notification\DriverNotificationService;
use LegacyFighter\Cabs\Repository\ClientRepository;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use LegacyFighter\Cabs\Tests\Double\FakeAppProperties;
use LegacyFighter\Cabs\TransitDetails\TransitDetailsFacade;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClaimAutomaticResolvingIntegrationTest extends KernelTestCase
{
    private ClaimService $claimService;
    private FakeAppProperties $appProperties;
    private Fixtures $fixtures;
    private AwardsService|MockObject $awardsService;
    private DriverNotificationService|MockObject $driverNotificationService;
    private ClientNotificationService|MockObject $clientNotificationService;

    protected function setUp(): void
    {
        $this->appProperties = new FakeAppProperties();
        $this->claimService = new ClaimService(
            $this->getContainer()->get(Clock::class),
            $this->getContainer()->get(ClientRepository::class),
            $this->getContainer()->get(TransitDetailsFacade::class),
            $this->getContainer()->get(ClaimRepository::class),
            $this->getContainer()->get(ClaimNumberGenerator::class),
            $this->appProperties,
            $this->awardsService = $this->createMock(AwardsService::class),
            $this->clientNotificationService = $this->createMock(ClientNotificationService::class),
            $this->driverNotificationService = $this->createMock(DriverNotificationService::class),
            $this->getContainer()->get(ClaimsResolverRepository::class)
        );
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
    }

    /**
     * @test
     */
    public function secondClaimForTheSameTransitWillBeEscalated(): void
    {
        //given
        $this->lowCostThresholdIs(40);
        //and
        $driver = $this->fixtures->aNearbyDriver();
        //and
        $client = $this->fixtures->aClient(Client::TYPE_VIP);
        //and
        $transit = $this->aTransit($client, $driver, 39);
        //and
        $claim = $this->fixtures->createClaim($client, $transit);
        //and
        $claim = $this->claimService->tryToResolveAutomatically($claim->getId());
        //and
        $claim2 = $this->fixtures->createClaim($client, $transit);

        //when
        $claim2 = $this->claimService->tryToResolveAutomatically($claim2->getId());

        //then
        self::assertEquals(Claim::STATUS_REFUNDED, $claim->getStatus());
        self::assertEquals(Claim::COMPLETION_MODE_AUTOMATIC, $claim->getCompletionMode());
        self::assertEquals(Claim::STATUS_ESCALATED, $claim2->getStatus());
        self::assertEquals(Claim::COMPLETION_MODE_MANUAL, $claim2->getCompletionMode());
    }

    /**
     * @test
     */
    public function lowCostTransitsAreRefundedIfClientIsVIP(): void
    {
        //given
        $this->lowCostThresholdIs(40);
        //and
        $driver = $this->fixtures->aNearbyDriver();
        //and
        $client = $this->aClientWithClaims(Client::TYPE_VIP, 3);
        //and
        $transit = $this->aTransit($client, $driver, 39);
        //and
        $claim = $this->fixtures->createClaim($client, $transit);

        //then
        $this->awardsService->expects($this->once())->method('registerNonExpiringMiles')->with($client->getId(), 10);
        $this->clientNotificationService->expects($this->once())->method('notifyClientAboutRefund')->with($claim->getClaimNo(), $client->getId());

        //when
        $claim = $this->claimService->tryToResolveAutomatically($claim->getId());

        //then
        self::assertEquals(Claim::STATUS_REFUNDED, $claim->getStatus());
        self::assertEquals(Claim::COMPLETION_MODE_AUTOMATIC, $claim->getCompletionMode());
    }

    /**
     * @test
     */
    public function highCostTransitsAreEscalatedEvenWhenClientIsVIP(): void
    {
        //given
        $this->lowCostThresholdIs(40);
        //and
        $driver = $this->fixtures->aNearbyDriver();
        //and
        $client = $this->aClientWithClaims(Client::TYPE_VIP, 3);
        //and
        $transit = $this->aTransit($client, $driver, 50);
        //and
        $claim = $this->fixtures->createClaim($client, $transit);

        //then
        $this->awardsService->expects($this->never())->method('registerNonExpiringMiles');
        $this->driverNotificationService->expects($this->once())->method('askDriverForDetailsAboutClaim')->with($claim->getClaimNo(), $driver->getId());

        //when
        $claim = $this->claimService->tryToResolveAutomatically($claim->getId());

        //then
        self::assertEquals(Claim::STATUS_ESCALATED, $claim->getStatus());
        self::assertEquals(Claim::COMPLETION_MODE_MANUAL, $claim->getCompletionMode());
    }

    /**
     * @test
     */
    public function firstThreeClaimsAreRefunded(): void
    {
        //given
        $this->lowCostThresholdIs(40);
        //and
        $this->noOfTransitsForAutomaticRefundIs(10);
        //and
        $client = $this->fixtures->aClient(Client::TYPE_NORMAL);
        //and
        $driver = $this->fixtures->aNearbyDriver();

        //then
        $this->awardsService->expects($this->never())->method('registerNonExpiringMiles');
        $this->clientNotificationService->expects($this->exactly(3))->method('notifyClientAboutRefund');

        //when
        $claim1 = $this->claimService->tryToResolveAutomatically($this->fixtures->createClaim($client, $this->aTransit($client, $driver, 50))->getId());
        $claim2 = $this->claimService->tryToResolveAutomatically($this->fixtures->createClaim($client, $this->aTransit($client, $driver, 50))->getId());
        $claim3 = $this->claimService->tryToResolveAutomatically($this->fixtures->createClaim($client, $this->aTransit($client, $driver, 50))->getId());
        $claim4 = $this->claimService->tryToResolveAutomatically($this->fixtures->createClaim($client, $this->aTransit($client, $driver, 50))->getId());

        //then
        self::assertEquals(Claim::STATUS_REFUNDED, $claim1->getStatus());
        self::assertEquals(Claim::STATUS_REFUNDED, $claim2->getStatus());
        self::assertEquals(Claim::STATUS_REFUNDED, $claim3->getStatus());
        self::assertEquals(Claim::STATUS_ESCALATED, $claim4->getStatus());
        self::assertEquals(Claim::COMPLETION_MODE_AUTOMATIC, $claim1->getCompletionMode());
        self::assertEquals(Claim::COMPLETION_MODE_AUTOMATIC, $claim2->getCompletionMode());
        self::assertEquals(Claim::COMPLETION_MODE_AUTOMATIC, $claim3->getCompletionMode());
        self::assertEquals(Claim::COMPLETION_MODE_MANUAL, $claim4->getCompletionMode());
    }

    /**
     * @test
     */
    public function lowCostTransitsAreRefundedWhenManyTransits(): void
    {
        //given
        $this->lowCostThresholdIs(40);
        //and
        $this->noOfTransitsForAutomaticRefundIs(10);
        //and
        $client = $this->aClientWithClaims(Client::TYPE_NORMAL, 3);
        //and
        $this->fixtures->clientHasDoneTransits($client, 12);
        //and
        $transit = $this->aTransit($client, $this->fixtures->aNearbyDriver(), 39);
        //and
        $claim = $this->fixtures->createClaim($client, $transit);

        //then
        $this->awardsService->expects($this->never())->method('registerNonExpiringMiles');
        $this->clientNotificationService->expects($this->once())->method('notifyClientAboutRefund')->with($claim->getClaimNo(), $client->getId());

        //when
        $claim = $this->claimService->tryToResolveAutomatically($claim->getId());

        //then
        self::assertEquals(Claim::STATUS_REFUNDED, $claim->getStatus());
        self::assertEquals(Claim::COMPLETION_MODE_AUTOMATIC, $claim->getCompletionMode());
    }

    /**
     * @test
     */
    public function highCostTransitsAreEscalatedEvenWithManyTransits(): void
    {
        //given
        $this->lowCostThresholdIs(40);
        //and
        $this->noOfTransitsForAutomaticRefundIs(10);
        //and
        $client = $this->aClientWithClaims(Client::TYPE_NORMAL, 3);
        //and
        $this->fixtures->clientHasDoneTransits($client, 12);
        //and
        $transit = $this->aTransit($client, $this->fixtures->aNearbyDriver(), 50);
        //and
        $claim = $this->fixtures->createClaim($client, $transit);

        //then
        $this->awardsService->expects($this->never())->method('registerNonExpiringMiles');
        $this->clientNotificationService->expects($this->once())->method('askForMoreInformation')->with($claim->getClaimNo(), $client->getId());

        //when
        $claim = $this->claimService->tryToResolveAutomatically($claim->getId());

        //then
        self::assertEquals(Claim::STATUS_ESCALATED, $claim->getStatus());
        self::assertEquals(Claim::COMPLETION_MODE_MANUAL, $claim->getCompletionMode());
    }

    /**
     * @test
     */
    public function highCostTransitsAreEscalatedWhenFewTransits(): void
    {
        //given
        $this->lowCostThresholdIs(40);
        //and
        $this->noOfTransitsForAutomaticRefundIs(10);
        //and
        $client = $this->fixtures->aClientWithClaims(Client::TYPE_NORMAL, 3);
        //and
        $this->fixtures->clientHasDoneTransits($client, 2);
        //and
        $driver = $this->fixtures->aNearbyDriver();
        //and
        $claim = $this->fixtures->createClaim($client, $this->fixtures->aJourney(50, $client, $driver, $this->fixtures->anAddress(), $this->fixtures->anAddress()));

        //then
        $this->awardsService->expects($this->never())->method('registerNonExpiringMiles');
        $this->driverNotificationService->expects($this->once())->method('askDriverForDetailsAboutClaim')->with($claim->getClaimNo(), $driver->getId());

        //when
        $claim = $this->claimService->tryToResolveAutomatically($claim->getId());

        //then
        self::assertEquals(Claim::STATUS_ESCALATED, $claim->getStatus());
        self::assertEquals(Claim::COMPLETION_MODE_MANUAL, $claim->getCompletionMode());
    }

    private function aTransit(Client $client, Driver $driver, int $price): Transit
    {
        return $this->fixtures->aJourney($price, $client, $driver, $this->fixtures->anAddress(), $this->fixtures->anAddress());
    }

    private function noOfTransitsForAutomaticRefundIs(int $no): void
    {
        $this->appProperties->setNoOfTransitsForClaimAutomaticRefund($no);
    }

    private function lowCostThresholdIs(int $price): void
    {
        $this->appProperties->setAutomaticRefundForVipThreshold($price);
    }

    private function aClientWithClaims(string $type, int $howManyClaims): Client
    {
        return $this->fixtures->aClientWithClaims($type, $howManyClaims);
    }

    public function createClaim(Client $client, Transit $transit): Claim
    {
        $claimDto = ClaimDTO::with('Okradli mnie na hajs', '$$$', $client->getId(), $transit->getId());
        $claimDto->setIsDraft(false);
        return $this->claimService->create($claimDto);
    }
}
