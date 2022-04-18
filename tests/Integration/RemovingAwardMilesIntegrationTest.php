<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Common\Clock;
use LegacyFighter\Cabs\Config\AppProperties;
use LegacyFighter\Cabs\Entity\Client;
use LegacyFighter\Cabs\Entity\Miles\AwardedMiles;
use LegacyFighter\Cabs\Repository\AwardsAccountRepository;
use LegacyFighter\Cabs\Service\AwardsService;
use LegacyFighter\Cabs\Tests\Common\FixedClock;
use LegacyFighter\Cabs\Tests\Common\Fixtures;
use LegacyFighter\Cabs\Tests\Double\FakeAppProperties;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RemovingAwardMilesIntegrationTest extends KernelTestCase
{
    private AwardsService $awardsService;
    private AwardsAccountRepository $awardsAccountRepository;
    private Fixtures $fixtures;
    private FixedClock $clock;
    private FakeAppProperties $appProperties;
    private const TRANSIT_ID = 1;

    protected function setUp(): void
    {
        $this->awardsService = $this->getContainer()->get(AwardsService::class);
        $this->appProperties = $this->getContainer()->get(AppProperties::class);
        $this->clock = $this->getContainer()->get(Clock::class);
        $this->awardsAccountRepository = $this->getContainer()->get(AwardsAccountRepository::class);
        $this->fixtures = $this->getContainer()->get(Fixtures::class);
    }

    /**
     * @test
     */
    public function byDefaultRemoveOldestFirstEvenWhenTheyAreNonExpiring(): void
    {
        //given
        $client = $this->clientWithAnActiveMilesProgram(Client::TYPE_NORMAL);
        //add
        $middle = $this->grantedMilesThatWillExpireInDays(10, 365, $this->yesterday(), $client);
        $youngest = $this->grantedMilesThatWillExpireInDays(10, 365, $this->today(), $client);
        $oldestNonExpiringMiles = $this->grantedNonExpiringMiles(5, $this->dayBeforeYesterday(), $client);

        //when
        $this->awardsService->removeMiles($client->getId(), 16);

        //then
        $awardedMiles = $this->awardsAccountRepository->findAllMilesBy($client);
        self::assertThatMilesWereReducedTo($oldestNonExpiringMiles, 0, $awardedMiles);
        self::assertThatMilesWereReducedTo($middle, 0, $awardedMiles);
        self::assertThatMilesWereReducedTo($youngest, 9, $awardedMiles);
    }

    /**
     * @test
     */
    public function shouldRemoveOldestMilesFirstWhenManyTransits(): void
    {
        //given
        $client = $this->clientWithAnActiveMilesProgram(Client::TYPE_NORMAL);
        //and
        $this->fixtures->clientHasDoneTransits($client, 15);
        //add
        $oldest = $this->grantedMilesThatWillExpireInDays(10, 60, $this->dayBeforeYesterday(), $client);
        $middle = $this->grantedMilesThatWillExpireInDays(10, 365, $this->yesterday(), $client);
        $youngest = $this->grantedMilesThatWillExpireInDays(10, 30, $this->today(), $client);

        //when
        $this->awardsService->removeMiles($client->getId(), 15);

        //then
        $awardedMiles = $this->awardsAccountRepository->findAllMilesBy($client);
        self::assertThatMilesWereReducedTo($oldest, 0, $awardedMiles);
        self::assertThatMilesWereReducedTo($middle, 5, $awardedMiles);
        self::assertThatMilesWereReducedTo($youngest, 10, $awardedMiles);
    }

    /**
     * @test
     */
    public function shouldRemoveNoNExpiringMilesLastWhenManyTransits(): void
    {
        //given
        $client = $this->clientWithAnActiveMilesProgram(Client::TYPE_NORMAL);
        //and
        $this->fixtures->clientHasDoneTransits($client, 15);
        //add
        $regularMiles = $this->grantedMilesThatWillExpireInDays(10, 365, $this->today(), $client);
        $oldestNonExpiringMiles = $this->grantedNonExpiringMiles(5, $this->dayBeforeYesterday(), $client);

        //when
        $this->awardsService->removeMiles($client->getId(), 13);

        //then
        $awardedMiles = $this->awardsAccountRepository->findAllMilesBy($client);
        self::assertThatMilesWereReducedTo($regularMiles, 0, $awardedMiles);
        self::assertThatMilesWereReducedTo($oldestNonExpiringMiles, 2, $awardedMiles);
    }

    /**
     * @test
     */
    public function shouldRemoveSoonToExpireMilesFirstWhenClientIsVIP(): void
    {
        //given
        $client = $this->clientWithAnActiveMilesProgram(Client::TYPE_VIP);
        //add
        $secondToExpire = $this->grantedMilesThatWillExpireInDays(10, 60, $this->yesterday(), $client);
        $thirdToExpire = $this->grantedMilesThatWillExpireInDays(5, 365, $this->dayBeforeYesterday(), $client);
        $firstToExpire = $this->grantedMilesThatWillExpireInDays(15, 30, $this->today(), $client);
        $nonExpiring = $this->grantedNonExpiringMiles(1, $this->dayBeforeYesterday(), $client);

        //when
        $this->awardsService->removeMiles($client->getId(), 21);

        //then
        $awardedMiles = $this->awardsAccountRepository->findAllMilesBy($client);
        self::assertThatMilesWereReducedTo($nonExpiring, 1, $awardedMiles);
        self::assertThatMilesWereReducedTo($firstToExpire, 0, $awardedMiles);
        self::assertThatMilesWereReducedTo($secondToExpire, 4, $awardedMiles);
        self::assertThatMilesWereReducedTo($thirdToExpire, 5, $awardedMiles);
    }

    /**
     * @test
     */
    public function shouldRemoveSoonToExpireMilesFirstWhenRemovingOnSundayAndClientHasDoneManyTransits(): void
    {
        //given
        $client = $this->clientWithAnActiveMilesProgram(Client::TYPE_NORMAL);
        //and
        $this->fixtures->clientHasDoneTransits($client, 15);
        //add
        $secondToExpire = $this->grantedMilesThatWillExpireInDays(10, 60, $this->yesterday(), $client);
        $thirdToExpire = $this->grantedMilesThatWillExpireInDays(5, 365, $this->dayBeforeYesterday(), $client);
        $firstToExpire = $this->grantedMilesThatWillExpireInDays(15, 10, $this->today(), $client);
        $nonExpiring = $this->grantedNonExpiringMiles(100, $this->yesterday(), $client);

        //when
        $this->itIsSunday();
        $this->awardsService->removeMiles($client->getId(), 21);

        //then
        $awardedMiles = $this->awardsAccountRepository->findAllMilesBy($client);
        self::assertThatMilesWereReducedTo($nonExpiring, 100, $awardedMiles);
        self::assertThatMilesWereReducedTo($firstToExpire, 0, $awardedMiles);
        self::assertThatMilesWereReducedTo($secondToExpire, 4, $awardedMiles);
        self::assertThatMilesWereReducedTo($thirdToExpire, 5, $awardedMiles);
    }

    /**
     * @test
     */
    public function shouldRemoveExpiringMilesFirstWhenClientHasManyClaims(): void
    {
        //given
        $client = $this->clientWithAnActiveMilesProgram(Client::TYPE_NORMAL);
        //and
        $this->fixtures->clientHasDoneClaims($client, 3);
        //add
        $secondToExpire = $this->grantedMilesThatWillExpireInDays(4, 60, $this->yesterday(), $client);
        $thirdToExpire = $this->grantedMilesThatWillExpireInDays(10, 365, $this->dayBeforeYesterday(), $client);
        $firstToExpire = $this->grantedMilesThatWillExpireInDays(5, 10, $this->yesterday(), $client);
        $nonExpiring = $this->grantedNonExpiringMiles(10, $this->yesterday(), $client);

        //when
        $this->awardsService->removeMiles($client->getId(), 21);

        //then
        $awardedMiles = $this->awardsAccountRepository->findAllMilesBy($client);
        self::assertThatMilesWereReducedTo($nonExpiring, 0, $awardedMiles);
        self::assertThatMilesWereReducedTo($firstToExpire, 5, $awardedMiles);
        self::assertThatMilesWereReducedTo($secondToExpire, 3, $awardedMiles);
        self::assertThatMilesWereReducedTo($thirdToExpire, 0, $awardedMiles);
    }

    /**
     * @param AwardedMiles[] $allMiles
     */
    private static function assertThatMilesWereReducedTo(AwardedMiles $firstToExpire, int $milesAfterReduction, array $allMiles): void
    {
        $actual = array_values(array_map(
            fn(AwardedMiles $am) => $am->getMilesAmount(new \DateTimeImmutable('0000-01-01')),
            array_filter($allMiles, fn(AwardedMiles $am) => $firstToExpire->getId() === $am->getId())
        ));
        self::assertEquals($milesAfterReduction, $actual[0]);
    }

    private function grantedMilesThatWillExpireInDays(int $miles, int $expirationDays, \DateTimeImmutable $when, Client $client): AwardedMiles
    {
        $this->milesWillExpireInDays($expirationDays);
        $this->defaultMilesBonusIs($miles);
        return $this->milesRegisteredAt($when, $client);
    }

    private function grantedNonExpiringMiles(int $miles, \DateTimeImmutable $when, Client $client): AwardedMiles
    {
        $this->defaultMilesBonusIs($miles);
        $this->clock->setDateTime($when);
        return $this->awardsService->registerNonExpiringMiles($client->getId(), $miles);
    }

    private function milesRegisteredAt(\DateTimeImmutable $when, Client $client): AwardedMiles
    {
        $this->clock->setDateTime($when);
        return $this->awardsService->registerMiles($client->getId(), self::TRANSIT_ID);
    }

    private function clientWithAnActiveMilesProgram(string $type): Client
    {
        $this->clock->setDateTime($this->dayBeforeYesterday());
        $client = $this->fixtures->aClient($type);
        $this->fixtures->activeAwardsAccount($client);
        return $client;
    }

    private function dayBeforeYesterday(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('1989-12-12 12:12');
    }

    private function yesterday(): \DateTimeImmutable
    {
        return $this->dayBeforeYesterday()->modify('+1 day');
    }

    private function today(): \DateTimeImmutable
    {
        return $this->yesterday()->modify('+1 day');
    }

    private function sunday(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('1989-12-17 12:12');
    }

    private function itIsSunday(): void
    {
        $this->clock->setDateTime($this->sunday());
    }

    private function milesWillExpireInDays(int $days): void
    {
        $this->appProperties->setMilesExpirationInDays($days);
    }

    private function defaultMilesBonusIs(int $miles): void
    {
        $this->appProperties->setDefaultMilesBonus($miles);
    }
}
