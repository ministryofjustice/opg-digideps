<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Sync\Command;

use OPG\Digideps\Frontend\Service\Client\Internal\ReportApi;
use OPG\Digideps\Frontend\Service\ParameterStoreService;
use OPG\Digideps\Frontend\Sync\Command\ChecklistSyncCommand;
use OPG\Digideps\Frontend\Sync\Service\ChecklistSyncService;
use OPG\Digideps\Frontend\TestHelpers\ChecklistTestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ChecklistSyncCommandTest extends KernelTestCase
{
    private ChecklistSyncService&MockObject $syncService;
    private MockObject&ParameterStoreService $parameterStore;
    private MockObject&ReportApi $reportApi;

    private CommandTester $commandTester;

    private ?string $output = null;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->syncService = $this->createMock(ChecklistSyncService::class);
        $this->parameterStore = $this->getMockBuilder(ParameterStoreService::class)->disableOriginalConstructor()->getMock();
        $this->reportApi = $this->getMockBuilder(ReportApi::class)->disableOriginalConstructor()->getMock();

        $checklistSyncCommand = new ChecklistSyncCommand($this->syncService, $this->parameterStore, $this->reportApi);
        $app->add($checklistSyncCommand);

        $commandName = $checklistSyncCommand->getName();
        self::assertIsString($commandName);
        $command = $app->find($commandName);

        $this->commandTester = new CommandTester($command);
    }

    public function testDoesNotSyncIfFeatureIsNotEnabled(): void
    {
        $this->ensureFeatureIsDisabled()
            ->assertSyncServiceIsNotInvoked()
            ->invokeTest();
    }

    private function invokeTest(): static
    {
        $this->commandTester->execute([]);

        $this->output = $this->commandTester->getDisplay();

        return $this;
    }

    public function testOutputContainsExpectedText(): void
    {
        $this->ensureFeatureIsEnabled()
            ->ensureThereAreNChecklistsToSync(3)
            ->ensureNChecklistsFailedToSync(2)
            ->invokeTest()
            ->assertCommandOutputContains('3 checklists to upload')
            ->assertCommandOutputContains('sync_checklists_to_sirius - failure - 2 checklists failed to sync');
    }

    public function testOutputContainsExpectedTextSuccess(): void
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureThereAreNChecklistsToSync(2)
            ->ensureNChecklistsFailedToSync(0)
            ->invokeTest()
            ->assertCommandOutputContains('2 checklists to upload')
            ->assertCommandOutputContains('sync_checklists_to_sirius - success - Sync command completed');
    }

    private function assertSyncServiceIsNotInvoked(): static
    {
        $this->reportApi
            ->expects($this->never())
            ->method('getReportsWithQueuedChecklists');

        $this->syncService
            ->expects($this->never())
            ->method('syncChecklistsByReports');

        return $this;
    }

    private function ensureFeatureIsDisabled(): static
    {
        $this->parameterStore
            ->method('getFeatureFlag')
            ->willReturn('0');

        return $this;
    }

    private function ensureThereAreNChecklistsToSync(int $numberOfChecklists): static
    {
        $reports = [];

        foreach (range(1, $numberOfChecklists) as $index) {
            $reports[] = ChecklistTestHelper::buildPfaHighReport($index, 'test@test.com', 'case-number');
        }

        $this->reportApi->method('getReportsWithQueuedChecklists')->willReturn($reports);

        return $this;
    }

    private function ensureNChecklistsFailedToSync(int $numberOfChecklists): static
    {
        $this->syncService->method('syncChecklistsByReports')->willReturn(['notSyncedCount' => $numberOfChecklists, 'reportIdsWithNullChecklists' => []]);

        return $this;
    }

    private function assertCommandOutputContains(string $outputContent): static
    {
        self::assertStringContainsString($outputContent, $this->output);

        return $this;
    }

    private function ensureFeatureIsEnabled(): static
    {
        $this->parameterStore
            ->method('getFeatureFlag')
            ->willReturn('1');

        return $this;
    }
}
