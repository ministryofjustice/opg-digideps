<?php
namespace App\Tests\Command;

use AppBundle\Command\ChecklistSyncCommand;
use AppBundle\Model\Sirius\QueuedChecklistData;
use AppBundle\Service\ChecklistSyncService;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\ParameterStoreService;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChecklistSyncCommandTest extends KernelTestCase
{
    /** @var MockObject */
    private $syncService, $parameterStore, $restClient;

    /** @var ContainerInterface */
    private $container;

    /** @var CommandTester */
    private $commandTester;

    public function setUp(): void
    {
        $this->syncService = $this->createMock(ChecklistSyncService::class);
        $this->parameterStore = $this->getMockBuilder(ParameterStoreService::class)->disableOriginalConstructor()->getMock();
        $this->restClient = $this->getMockBuilder(RestClient::class)->disableOriginalConstructor()->getMock();

        $kernel = static::bootKernel([ 'debug' => false ]);
        $this->container = $kernel->getContainer();
        $this->container->set(ChecklistSyncService::class, $this->syncService);
        $this->container->set(RestClient::class, $this->restClient);
        $this->container->set(ParameterStoreService::class, $this->parameterStore);
        $application = new Application($kernel);

        $command = $application->find('digideps:checklist-sync');
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

    /**
     * @test
     */
    public function fetchesAndSendsQueuedChecklistsToSyncService()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureRestClientReturnsRows()
            ->assertEachRowWillBeTransformedAndSentToSyncService()
            ->assertStatusesWillNotBeSetToError()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function fetchesAconfigurableLimitOfChecklists()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureConfigurableRowLimitIsSetTo('30')
            ->assertChecklistsAreFetchedWithLimitOf('30')
            ->invokeTest();
    }

    /**
     * @test
     */
    public function fetchesDefaultLimitOfChecklistsOfConfigurableValueNotSet()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureConfigurableRowLimitIsNotSet()
            ->assertChecklistsAreFetchedWithDefaultLimit()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function updatesSyncStatusForFailedSyncs()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureRestClientReturnsRows()
            ->ensureErrorsWillOccur()
            ->assertStatusesWillBeUpdatedBySyncService()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function resetsCountOfChecklistsNotSynched()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureRestClientReturnsRows()
            ->ensureChecklistsAreNotSynched()
            ->assertNotSynchedCountIsReset()
            ->invokeTest();
    }

    private function ensureFeatureIsEnabled(): ChecklistSyncCommandTest
    {
        $this->parameterStore
            ->method('getFeatureFlag')
            ->willReturn('1');

        return $this;
    }

    private function ensureFeatureIsDisabled(): ChecklistSyncCommandTest
    {
        $this->parameterStore
            ->method('getFeatureFlag')
            ->willReturn('0');

        return $this;
    }

    private function ensureConfigurableRowLimitIsSetTo(string $limit): ChecklistSyncCommandTest
    {
        $this->parameterStore
            ->method('getParameter')
            ->with(ParameterStoreService::PARAMETER_CHECKLIST_SYNC_ROW_LIMIT)
            ->willReturn($limit);

        return $this;
    }

    private function ensureConfigurableRowLimitIsNotSet(): ChecklistSyncCommandTest
    {
        $this->parameterStore
            ->method('getParameter')
            ->with(ParameterStoreService::PARAMETER_CHECKLIST_SYNC_ROW_LIMIT)
            ->willReturn(null);

        return $this;
    }

    private function ensureRestClientReturnsRows(): ChecklistSyncCommandTest
    {
        $this->restClient
            ->method('apiCall')
            ->with('get', 'checklist/queued', ['row_limit' => '100'], 'array', [], false)
            ->willReturn(json_encode([['checklist_id' => 4391], ['checklist_id' => 3904]]));

        return $this;
    }

    private function assertChecklistsAreFetchedWithLimitOf(string $limit)
    {
        $this->restClient
            ->expects($this->once())
            ->method('apiCall')
            ->with('get', 'checklist/queued', ['row_limit' => $limit], 'array', [], false)
            ->willReturn(json_encode([['checklist_id' => 4391], ['checklist_id' => 3904]]));

        return $this;
    }

    private function assertChecklistsAreFetchedWithDefaultLimit()
    {
        $this->restClient
            ->expects($this->once())
            ->method('apiCall')
            ->with('get', 'checklist/queued', ['row_limit' => ChecklistSyncCommand::FALLBACK_ROW_LIMITS], 'array', [], false)
            ->willReturn(json_encode([['checklist_id' => 4391], ['checklist_id' => 3904]]));

        return $this;
    }

    private function assertEachRowWillBeTransformedAndSentToSyncService(): ChecklistSyncCommandTest
    {
        $this->syncService
            ->expects($this->exactly(2))
            ->method('sync')
            ->withConsecutive(
                [$this->isInstanceOf(QueuedChecklistData::class)],
                [$this->isInstanceOf(QueuedChecklistData::class)]
            );

        return $this;
    }

    private function assertStatusesWillNotBeSetToError(): ChecklistSyncCommandTest
    {
        $this->syncService
            ->expects($this->never())
            ->method('setChecklistsToPermanentError');

        return $this;
    }

    private function ensureErrorsWillOccur(): ChecklistSyncCommandTest
    {
        $this->syncService
            ->method('getSyncErrorSubmissionIds')
            ->willReturn(['error-1', 'error-2']);

        return $this;
    }

    private function ensureChecklistsAreNotSynched(): ChecklistSyncCommandTest
    {
        $this->syncService
            ->method('getChecklistsNotSyncedCount')
            ->willReturn(1);

        return $this;
    }

    private function assertStatusesWillBeUpdatedBySyncService(): ChecklistSyncCommandTest
    {
        $this->syncService
            ->expects($this->once())
            ->method('setChecklistsToPermanentError');

        $this->syncService
            ->expects($this->once())
            ->method('setSyncErrorSubmissionIds');

        return $this;
    }

    private function assertNotSynchedCountIsReset(): ChecklistSyncCommandTest
    {
        $this->syncService
            ->expects($this->once())
            ->method('setChecklistsNotSyncedCount')
            ->with(0);

        return $this;
    }

    private function assertSyncServiceIsNotInvoked(): ChecklistSyncCommandTest
    {
        $this->syncService
            ->expects($this->never())
            ->method('sync');

        return $this;
    }

    private function invokeTest(): void
    {
        $this->commandTester->execute([]);
    }
}
