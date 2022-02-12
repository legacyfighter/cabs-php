<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use LegacyFighter\Cabs\Repository\AwardedMilesRepository;
use LegacyFighter\Cabs\Service\AwardsService;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AwardMilesManagementIntegrationTest extends KernelTestCase
{
    private AwardsService $awardsService;
    private AwardedMilesRepository $awardedMilesRepository;
    private Fixtures $fixtures;

    protected function setUp(): void
    {
        $this->awardsService = $this->getContainer()->get(AwardsService::class);
        $this->awardedMilesRepository = $this->getContainer()->get(AwardedMilesRepository::class);
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
    }

    /**
     * @test
     */
    public function canRegisterAccount(): void
    {
        //given
        $client = $this->fixtures->aClient();

        //when
        $this->awardsService->registerToProgram($client->getId());

        //then
        $account = $this->awardsService->findBy($client->getId());
        self::assertNotNull($account);
        self::assertEquals($client->getId(), $account->getClient()->getId());
        self::assertFalse($account->isActive());
        self::assertEquals(0, $account->getTransactions());
    }

    /**
     * @test
     */
    public function canActivateAccount(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->awardsService->registerToProgram($client->getId());

        //when
        $this->awardsService->activateAccount($client->getId());

        //then
        $account = $this->awardsService->findBy($client->getId());
        self::assertTrue($account->isActive());
    }

    /**
     * @test
     */
    public function canDeactivateAccount(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->fixtures->activeAwardsAccount($client);

        //when
        $this->awardsService->deactivateAccount($client->getId());

        //then
        $account = $this->awardsService->findBy($client->getId());
        self::assertFalse($account->isActive());
    }

    /**
     * @test
     */
    public function canRegisterMiles(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->fixtures->activeAwardsAccount($client);
        //and
        $transit = $this->fixtures->aTransit(null, 80);

        //when
        $this->awardsService->registerMiles($client->getId(), $transit->getId());

        //then
        $account = $this->awardsService->findBy($client->getId());
        self::assertEquals(1, $account->getTransactions());
        $awardedMiles = $this->awardedMilesRepository->findAllByClient($client);
        self::assertCount(1, $awardedMiles);
        self::assertEquals(10, $awardedMiles[0]->getMilesAmount(new \DateTimeImmutable()));
        self::assertFalse($awardedMiles[0]->cantExpire());
    }

    /**
     * @test
     */
    public function canRegisterNonExpiringMiles(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->fixtures->activeAwardsAccount($client);

        //when
        $this->awardsService->registerNonExpiringMiles($client->getId(), 20);

        //then
        $account = $this->awardsService->findBy($client->getId());
        self::assertEquals(1, $account->getTransactions());
        $awardedMiles = $this->awardedMilesRepository->findAllByClient($client);
        self::assertCount(1, $awardedMiles);
        self::assertEquals(20, $awardedMiles[0]->getMilesAmount(new \DateTimeImmutable()));
        self::assertTrue($awardedMiles[0]->cantExpire());
    }

    /**
     * @test
     */
    public function canCalculateMilesBalance(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->fixtures->activeAwardsAccount($client);
        //and
        $transit = $this->fixtures->aTransit(null, 80);

        //when
        $this->awardsService->registerNonExpiringMiles($client->getId(), 20);
        $this->awardsService->registerMiles($client->getId(), $transit->getId());
        $this->awardsService->registerMiles($client->getId(), $transit->getId());

        //then
        $account = $this->awardsService->findBy($client->getId());
        self::assertEquals(3, $account->getTransactions());
        self::assertEquals(40, $this->awardsService->calculateBalance($client->getId()));
    }

    /**
     * @test
     */
    public function canTransferMiles(): void
    {
        //given
        $client = $this->fixtures->aClient();
        $secondClient = $this->fixtures->aClient();
        //and
        $this->fixtures->activeAwardsAccount($client);
        $this->fixtures->activeAwardsAccount($secondClient);
        //and
        $this->awardsService->registerNonExpiringMiles($client->getId(), 10);

        //when
        $this->awardsService->transferMiles($client->getId(), $secondClient->getId(), 10);

        //then
        $this->getContainer()->get(EntityManagerInterface::class)->clear();
        self::assertEquals(0, $this->awardsService->calculateBalance($client->getId()));
        self::assertEquals(10, $this->awardsService->calculateBalance($secondClient->getId()));
    }

    /**
     * @test
     */
    public function cannotTransferMilesWhenAccountIsNotActive(): void
    {
        //given
        $client = $this->fixtures->aClient();
        $secondClient = $this->fixtures->aClient();
        //and
        $this->fixtures->activeAwardsAccount($client);
        $this->fixtures->activeAwardsAccount($secondClient);
        //and
        $this->awardsService->registerNonExpiringMiles($client->getId(), 10);
        //and
        $this->awardsService->deactivateAccount($client->getId());

        //when
        $this->awardsService->transferMiles($client->getId(), $secondClient->getId(), 5);

        //then
        self::assertEquals(10, $this->awardsService->calculateBalance($client->getId()));
    }

    /**
     * @test
     */
    public function cannotTransferMilesWhenNotEnough(): void
    {
        //given
        $client = $this->fixtures->aClient();
        $secondClient = $this->fixtures->aClient();
        //and
        $this->fixtures->activeAwardsAccount($client);
        $this->fixtures->activeAwardsAccount($secondClient);
        //and
        $this->awardsService->registerNonExpiringMiles($client->getId(), 10);

        //when
        $this->awardsService->transferMiles($client->getId(), $secondClient->getId(), 30);

        //then
        self::assertEquals(10, $this->awardsService->calculateBalance($client->getId()));
    }

    /**
     * @test
     */
    public function canRemoveMiles(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->fixtures->activeAwardsAccount($client);
        //and
        $transit = $this->fixtures->aTransit(null, 80);
        //and
        $this->awardsService->registerMiles($client->getId(), $transit->getId());
        $this->awardsService->registerMiles($client->getId(), $transit->getId());
        $this->awardsService->registerMiles($client->getId(), $transit->getId());

        //when
        $this->awardsService->removeMiles($client->getId(), 20);

        //then
        self::assertEquals(10, $this->awardsService->calculateBalance($client->getId()));
    }

    /**
     * @test
     */
    public function cannotRemoveMoreThanClientHasMiles(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->fixtures->activeAwardsAccount($client);
        //and
        $transit = $this->fixtures->aTransit(null, 80);
        //and
        $this->awardsService->registerMiles($client->getId(), $transit->getId());
        $this->awardsService->registerMiles($client->getId(), $transit->getId());
        $this->awardsService->registerMiles($client->getId(), $transit->getId());

        //then
        $this->expectException(\InvalidArgumentException::class);

        //when
        $this->awardsService->removeMiles($client->getId(), 40);
    }

    /**
     * @test
     */
    public function cannotRemoveMilesIfAccountIsNotActive(): void
    {
        //given
        $client = $this->fixtures->aClient();
        //and
        $this->awardsService->registerToProgram($client->getId());
        //and
        $transit = $this->fixtures->aTransit(null, 80);
        //and
        $currentBalance = $this->awardsService->calculateBalance($client->getId());

        //when
        $this->awardsService->registerMiles($client->getId(), $transit->getId());


        //when
        self::assertEquals($currentBalance, $this->awardsService->calculateBalance($client->getId()));
    }
}
