<?php

namespace LegacyFighter\Cabs\Tests\Integration\Repair;

use LegacyFighter\Cabs\Party\Api\PartyId;
use LegacyFighter\Cabs\Repair\Api\ContractManager;
use LegacyFighter\Cabs\Repair\Api\RepairProcess;
use LegacyFighter\Cabs\Repair\Api\RepairRequest;
use LegacyFighter\Cabs\Repair\Legacy\Parts\Parts;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RepairProcessTest extends KernelTestCase
{
    private RepairProcess $repairProcess;
    private ContractManager $contractManager;
    private PartyId $vehicle;
    private PartyId $handlingParty;

    protected function setUp(): void
    {
        $this->repairProcess = $this->getContainer()->get(RepairProcess::class);
        $this->contractManager = $this->getContainer()->get(ContractManager::class);
        $this->vehicle = new PartyId();
        $this->handlingParty = new PartyId();
    }

    /**
     * @test
     */
    public function warrantyByInsuranceCoversAllButPaint(): void
    {
        //given
        $this->contractManager->extendedWarrantyContractSigned($this->handlingParty, $this->vehicle);
        $parts = [Parts::ENGINE, Parts::GEARBOX, Parts::PAINT, Parts::SUSPENSION];
        $repairRequest = new RepairRequest($this->vehicle, $parts);

        //when
        $result = $this->repairProcess->resolve($repairRequest);

        //then
        (new VehicleRepairAssert($result))->by($this->handlingParty)->free()->allPartsBut($parts, [Parts::PAINT]);
    }

    /**
     * @test
     */
    public function manufacturerWarrantyCoversAll(): void
    {
        //given
        $this->contractManager->manufacturerWarrantyRegistered($this->handlingParty, $this->vehicle);
        $parts = [Parts::ENGINE, Parts::GEARBOX, Parts::PAINT, Parts::SUSPENSION];
        $repairRequest = new RepairRequest($this->vehicle, $parts);

        //when
        $result = $this->repairProcess->resolve($repairRequest);

        //then
        (new VehicleRepairAssert($result))->by($this->handlingParty)->free()->allParts($parts);
    }
}
