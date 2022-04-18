<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Crm\Claims\ClaimService;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Repository\AwardsAccountRepository;
use LegacyFighter\Cabs\Repository\ClientRepository;
use LegacyFighter\Cabs\Repository\TransitRepository;
use LegacyFighter\Cabs\Service\AwardsService;
use LegacyFighter\Cabs\Service\AwardsServiceImpl;
use LegacyFighter\Cabs\Service\ClientService;
use LegacyFighter\Cabs\Tests\Common\FixedClock;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use LegacyFighter\Cabs\Tests\Double\FakeAppProperties;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExpiringMilesIntegrationTest extends KernelTestCase
{
    private AwardsService $awardsService;
    private Fixtures $fixtures;
    private FixedClock $clock;
    private FakeAppProperties $appProperties;
    private const TRANSIT_ID = 1;

    protected function setUp(): void
    {
        $this->awardsService = new AwardsServiceImpl(
            $this->getContainer()->get(AwardsAccountRepository::class),
            $this->getContainer()->get(TransitRepository::class),
            $this->clock = new FixedClock(),
            $this->appProperties = new FakeAppProperties(),
            $this->getContainer()->get(ClientService::class)
        );
        $this->awardsService->setClaimService($this->getContainer()->get(ClaimService::class));
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
    }

    /**
     * @test
     */
    public function shouldTakeIntoAccountExpiredMilesWhenCalculatingBalance(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->appProperties->setDefaultMilesBonus(10);
        //and
        $this->appProperties->setMilesExpirationInDays(365);
        //and
        $this->fixtures->activeAwardsAccount($client);

        //when
        $this->registerMilesAt($client, new \DateTimeImmutable('1989-12-12'));
        //then
        self::assertEquals(10, $this->calculateBalanceAt($client, new \DateTimeImmutable('1989-12-12')));
        //when
        $this->registerMilesAt($client, new \DateTimeImmutable('1989-12-13'));
        //then
        self::assertEquals(20, $this->calculateBalanceAt($client, new \DateTimeImmutable('1989-12-12')));
        //when
        $this->registerMilesAt($client, new \DateTimeImmutable('1989-12-14'));
        //then
        self::assertEquals(30, $this->calculateBalanceAt($client, new \DateTimeImmutable('1989-12-14')));
        self::assertEquals(30, $this->calculateBalanceAt($client, (new \DateTimeImmutable('1989-12-12'))->modify('+300 days')));
        self::assertEquals(20, $this->calculateBalanceAt($client, (new \DateTimeImmutable('1989-12-12'))->modify('+365 days')));
        self::assertEquals(10, $this->calculateBalanceAt($client, (new \DateTimeImmutable('1989-12-13'))->modify('+365 days')));
        self::assertEquals(0, $this->calculateBalanceAt($client, (new \DateTimeImmutable('1989-12-14'))->modify('+365 days')));
    }

    private function registerMilesAt(Client $client, \DateTimeImmutable $when): void
    {
        $this->clock->setDateTime($when);
        $this->awardsService->registerMiles($client->getId(), self::TRANSIT_ID);
    }

    private function calculateBalanceAt(Client $client, \DateTimeImmutable $when): int
    {
        $this->clock->setDateTime($when);
        return $this->awardsService->calculateBalance($client->getId());
    }
}
