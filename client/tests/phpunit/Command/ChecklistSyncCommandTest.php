<?php
namespace App\Tests\Command;

use AppBundle\Command\ChecklistSyncCommand;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use AppBundle\Exception\PdfGenerationFailedException;
use AppBundle\Exception\SiriusDocumentSyncFailedException;
use AppBundle\Model\Sirius\QueuedChecklistData;
use AppBundle\Service\ChecklistPdfGenerator;
use AppBundle\Service\ChecklistSyncService;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\ParameterStoreService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChecklistSyncCommandTest extends KernelTestCase
{
    /** @var MockObject */
    private $syncService, $parameterStore, $restClient, $pdfGenerator;

    /** @var ContainerInterface */
    private $container;

    /** @var CommandTester */
    private $commandTester;

    public function setUp(): void
    {
        $this->syncService = $this->createMock(ChecklistSyncService::class);
        $this->parameterStore = $this->getMockBuilder(ParameterStoreService::class)->disableOriginalConstructor()->getMock();
        $this->restClient = $this->getMockBuilder(RestClient::class)->disableOriginalConstructor()->getMock();
        $this->pdfGenerator = $this->getMockBuilder(ChecklistPdfGenerator::class)->disableOriginalConstructor()->getMock();

        $kernel = static::bootKernel([ 'debug' => false ]);
        $this->container = $kernel->getContainer();
        $this->container->set(ChecklistSyncService::class, $this->syncService);
        $this->container->set(RestClient::class, $this->restClient);
        $this->container->set(ParameterStoreService::class, $this->parameterStore);
        $this->container->set(ChecklistPdfGenerator::class, $this->pdfGenerator);
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
    public function updatesSyncStatusOnFailedPdfGenerations()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureRestClientReturnsRows()
            ->ensurePdfGenerationWillFailWith(new PdfGenerationFailedException('Failed to generate PDF'))
            ->assertChecklistStatusWillBeUpdatedWithError('Failed to generate PDF')
            ->invokeTest();
    }

    /**
     * @test
     */
    public function doesNotAttemptToSyncFailedPdfGenerations()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureRestClientReturnsRows()
            ->ensurePdfGenerationWillFailWith(new PdfGenerationFailedException('Failed to generate PDF'))
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
            ->ensurePdfGenerationWillSucceed()
            ->assertEachRowWillBeTransformedAndSentToSyncService()
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
            ->ensurePdfGenerationWillSucceed()
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
            ->ensurePdfGenerationWillSucceed()
            ->assertChecklistsAreFetchedWithDefaultLimit()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function updatesStatusAndUuidOfEachSuccessfullySyncedChecklist()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureConfigurableRowLimitIsNotSet()
            ->ensurePdfGenerationWillSucceed()
            ->assertChecklistsAreFetchedWithDefaultLimit()
            ->assertChecklistStatusWillBeUpdatedWithSuccess()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function updatesSyncStatusOnFailedDocumentSyncs()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureConfigurableRowLimitIsNotSet()
            ->ensurePdfGenerationWillSucceed()
            ->assertChecklistsAreFetchedWithDefaultLimit()
            ->ensureSyncWillFailWith(new SiriusDocumentSyncFailedException('Failed to sync document'))
            ->assertChecklistStatusWillBeUpdatedWithError('Failed to sync document')
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

    private function ensurePdfGenerationWillFailWith(\Throwable $e): ChecklistSyncCommandTest
    {
        $this->pdfGenerator
            ->method('generate')
            ->willThrowException($e);

        return $this;
    }

    private function ensurePdfGenerationWillSucceed(): ChecklistSyncCommandTest
    {
        $this->pdfGenerator
            ->method('generate')
            ->willReturn('file-contents');

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
            ->expects($this->at(0))
            ->method('apiCall')
            ->with('get', 'report/all-with-queued-checklists', ['row_limit' => '30'], 'Report\Report[]', [], false)
            ->willReturn([
                $this->buildReport(),
                $this->buildReport()
            ]);

        return $this;
    }

    private function assertChecklistsAreFetchedWithLimitOf(string $limit)
    {
        $this->restClient
            ->expects($this->at(0))
            ->method('apiCall')
            ->with('get', 'report/all-with-queued-checklists', ['row_limit' => $limit], 'Report\Report[]', [], false)
            ->willReturn([
                $this->buildReport(),
                $this->buildReport()
            ]);

        return $this;
    }

    private function assertChecklistsAreFetchedWithDefaultLimit()
    {
        $this->restClient
            ->expects($this->at(0))
            ->method('apiCall')
            ->with('get', 'report/all-with-queued-checklists', ['row_limit' => ChecklistSyncCommand::FALLBACK_ROW_LIMITS], 'Report\Report[]', [], false)
            ->willReturn([
                $this->buildReport(),
                $this->buildReport()
            ]);

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
            )
            ->willReturnOnConsecutiveCalls('uuid-1', 'uuid-2');

        return $this;
    }

    private function ensureSyncWillFailWith(\Throwable $e): ChecklistSyncCommandTest
    {
        $this->syncService
            ->expects($this->exactly(2))
            ->method('sync')
            ->withConsecutive(
                [$this->isInstanceOf(QueuedChecklistData::class)],
                [$this->isInstanceOf(QueuedChecklistData::class)]
            )
            ->willThrowException($e);

        return $this;
    }

    private function assertChecklistStatusWillBeUpdatedWithError($error): ChecklistSyncCommandTest
    {
        $this->restClient
            ->expects($this->at(1))
            ->method('apiCall')
            ->with(
                'put',
                'checklist/3923',
                json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_PERMANENT_ERROR,
                    'syncError' => $error
                ]),
                'raw',
                [],
                false
            );

        return $this;
    }

    private function assertChecklistStatusWillBeUpdatedWithSuccess(): ChecklistSyncCommandTest
    {
        $this->restClient
            ->expects($this->at(1))
            ->method('apiCall')
            ->with(
                'put',
                'checklist/3923',
                json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_SUCCESS
                ]),
                'raw',
                [],
                false
            );

        return $this;
    }

    private function assertSyncServiceIsNotInvoked(): ChecklistSyncCommandTest
    {
        $this->syncService
            ->expects($this->never())
            ->method('sync');

        return $this;
    }

    private function buildReport()
    {
        $report = new Report();
        $report->setStartDate(new \DateTime());
        $report->setEndDate(new \DateTime());
        $report->setReportSubmissions([]);
        $checklist = new Checklist($report);
        $report->setChecklist($checklist);
        $checklist->setId(3923);
        $client = new Client();
        $client->setCaseNumber('case-number');
        $report->setClient($client);

        return $report;
    }

    private function invokeTest(): void
    {
        $this->commandTester->execute([]);
    }
}
