<?php

namespace App\Tests\Command;

use App\Command\ChecklistSyncCommand;
use App\Service\ChecklistSyncService;
use App\Service\Client\Internal\ReportApi;
use App\Service\ParameterStoreService;
use App\TestHelpers\ChecklistTestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ChecklistSyncCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /** @var MockObject */
    private $syncService;
    private $parameterStore;
    private $reportApi;
    private $pdfGenerator;

    /** @var CommandTester */
    private $commandTester;

    private ?string $output = null;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->syncService = $this->createMock(ChecklistSyncService::class);
        $this->parameterStore = $this->getMockBuilder(ParameterStoreService::class)->disableOriginalConstructor()->getMock();
        $this->reportApi = $this->getMockBuilder(ReportApi::class)->disableOriginalConstructor()->getMock();

        $app->add(new ChecklistSyncCommand($this->syncService, $this->parameterStore, $this->reportApi));

        $command = $app->find(ChecklistSyncCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @test
     */
    public function doesNotSyncIfFeatureIsNotEnabled()
    {
        $this
            ->ensureFeatureIsDisabled()
            ->assertSyncServiceIsNotInvoked()
            ->invokeTest();
    }

    private function invokeTest(): self
    {
        $this->commandTester->execute([]);

        $this->output = $this->commandTester->getDisplay();

        return $this;
    }

    /**
     * @test
     */
    public function outputContainsExpectedText()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureThereAreNChecklistsToSync(3)
            ->ensureNChecklistsFailedToSync(2)
            ->invokeTest()
            ->assertCommandOutputContains('3 checklists to upload')
            ->assertCommandOutputContains('2 checklists failed to sync')
            ->assertCommandOutputContains('Sync command completed');
    }

    private function assertSyncServiceIsNotInvoked(): ChecklistSyncCommandTest
    {
        $this->reportApi
            ->expects($this->never())
            ->method('getReportsWithQueuedChecklists');

        $this->syncService
            ->expects($this->never())
            ->method('syncChecklistsByReports');

        return $this;
    }

    private function ensureFeatureIsDisabled(): ChecklistSyncCommandTest
    {
        $this->parameterStore
            ->method('getFeatureFlag')
            ->willReturn('0');

        return $this;
    }

    private function ensureThereAreNChecklistsToSync(int $numberOfChecklists): self
    {
        $reports = [];

        foreach (range(1, $numberOfChecklists) as $index) {
            $reports[] = ChecklistTestHelper::buildPfaHighReport($index, 'test@test.com', 'case-number');
        }

        $this->reportApi->method('getReportsWithQueuedChecklists')->willReturn($reports);

        return $this;
    }

    private function ensureNChecklistsFailedToSync(int $numberOfChecklists): self
    {
        $this->syncService->method('syncChecklistsByReports')->willReturn($numberOfChecklists);

        return $this;
    }

    private function assertCommandOutputContains(string $outputContent): self
    {
        self::assertStringContainsString($outputContent, $this->output);

        return $this;
    }

    private function ensureFeatureIsEnabled(): ChecklistSyncCommandTest
    {
        $this->parameterStore
            ->method('getFeatureFlag')
            ->willReturn('1');

        return $this;
    }
}
