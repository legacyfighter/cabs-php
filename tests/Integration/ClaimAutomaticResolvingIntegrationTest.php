<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\DTO\ClaimDTO;
use LegacyFighter\Cabs\Entity\Claim;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Repository\ClaimRepository;
use LegacyFighter\Cabs\Repository\ClaimsResolverRepository;
use LegacyFighter\Cabs\Repository\ClientRepository;
use LegacyFighter\Cabs\Repository\TransitRepository;
use LegacyFighter\Cabs\Service\AwardsService;
use LegacyFighter\Cabs\Service\ClaimNumberGenerator;
use LegacyFighter\Cabs\Service\ClaimService;
use LegacyFighter\Cabs\Service\ClientNotificationService;
use LegacyFighter\Cabs\Service\DriverNotificationService;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use LegacyFighter\Cabs\Tests\Double\FakeAppProperties;
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
            $this->getContainer()->get(TransitRepository::class),
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
        $driver = $this->fixtures->aDriver();
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
        $driver = $this->fixtures->aDriver();
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
        $driver = $this->fixtures->aDriver();
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
        $driver = $this->fixtures->aDriver();

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
        $transit = $this->aTransit($client, $this->fixtures->aDriver(), 39);
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
        $transit = $this->aTransit($client, $this->fixtures->aDriver(), 50);
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
        $driver = $this->fixtures->aDriver();
        //and
        $claim = $this->fixtures->createClaim($client, $this->fixtures->aTransit($driver, 50, new \DateTimeImmutable(), $client));

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
        return $this->fixtures->aTransit($driver, $price, new \DateTimeImmutable(), $client);
    }

    private function aClient(string $type): Client
    {
        return $this->fixtures->aClient($type);
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
        $client = $this->fixtures->aClient($type);
        foreach (range(1, $howManyClaims+1) as $_) {
            $claim = $this->createClaim($client, $this->fixtures->aTransit($this->fixtures->aDriver(), 20, new \DateTimeImmutable(), $client));
            $this->claimService->tryToResolveAutomatically($claim->getId());
        }
        return $client;
    }

    public function createClaim(Client $client, Transit $transit): Claim
    {
        $claimDto = ClaimDTO::with('Okradli mnie na hajs', '$$$', $client->getId(), $transit->getId());
        $claimDto->setIsDraft(false);
        return $this->claimService->create($claimDto);
    }
}
