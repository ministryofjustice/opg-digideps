<?php

namespace App\Tests\Command;

use App\Command\ChecklistSyncCommand;
use App\Entity\Client;
use App\Entity\Report\Checklist;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Exception\PdfGenerationFailedException;
use App\Exception\SiriusDocumentSyncFailedException;
use App\Model\Sirius\QueuedChecklistData;
use App\Service\ChecklistPdfGenerator;
use App\Service\ChecklistSyncService;
use App\Service\Client\RestClient;
use App\Service\ParameterStoreService;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Throwable;

class ChecklistSyncCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /** @var MockObject */
    private $syncService;
    private $parameterStore;
    private $restClient;
    private $pdfGenerator;

    /** @var CommandTester */
    private $commandTester;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->syncService = $this->createMock(ChecklistSyncService::class);
        $this->parameterStore = $this->getMockBuilder(ParameterStoreService::class)->disableOriginalConstructor()->getMock();
        $this->restClient = $this->getMockBuilder(RestClient::class)->disableOriginalConstructor()->getMock();
        $this->pdfGenerator = $this->getMockBuilder(ChecklistPdfGenerator::class)->disableOriginalConstructor()->getMock();

        $app->add(new ChecklistSyncCommand($this->pdfGenerator, $this->syncService, $this->restClient, $this->parameterStore));

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

    private function invokeTest(): void
    {
        $this->commandTester->execute([]);
    }

    private function assertSyncServiceIsNotInvoked(): ChecklistSyncCommandTest
    {
        $this->syncService
            ->expects($this->never())
            ->method('sync');

        return $this;
    }

    private function ensureFeatureIsDisabled(): ChecklistSyncCommandTest
    {
        $this->parameterStore
            ->method('getFeatureFlag')
            ->willReturn('0');

        return $this;
    }

    /**
     * @test
     */
    public function updatesSyncStatusOnFailedPdfGenerations()
    {
        $apiCallArguments = [
            [
                'get',
                'report/all-with-queued-checklists',
                ['row_limit' => '30'],
                'Report\Report[]',
                [],
                false,
            ],
            [
                'put',
                'checklist/3923',
                json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_PERMANENT_ERROR,
                    'syncError' => 'Failed to generate PDF',
                ]),
                'raw',
                [],
                false,
            ],
        ];

        $returnValues = [
            [],
            [
                $this->buildReport(),
                $this->buildReport(),
            ],
        ];

        $this
            ->ensureFeatureIsEnabled()
            ->assertApiCallsAreMade($apiCallArguments, $returnValues)
            ->ensurePdfGenerationWillFailWith(new PdfGenerationFailedException('Failed to generate PDF'))
            ->invokeTest();
    }

    private function buildReport()
    {
        $user = (new User())->setEmail('test@test.com');

        $report = (new Report())
            ->setStartDate(new DateTime())
            ->setEndDate(new DateTime())
            ->setReportSubmissions([])
            ->setType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS);

        $checklist = (new Checklist($report))->setSubmittedBy($user);
        $checklist->setId(3923);

        $report->setChecklist($checklist);

        $client = new Client();
        $client->setCaseNumber('case-number');

        $report->setClient($client);

        return $report;
    }

    private function ensurePdfGenerationWillFailWith(Throwable $e): ChecklistSyncCommandTest
    {
        $this->pdfGenerator
            ->method('generate')
            ->willThrowException($e);

        return $this;
    }

    private function assertApiCallsAreMade(array $arguments, array $returnValues): ChecklistSyncCommandTest
    {
        $this->restClient
            ->expects($this->once())
            ->method('apiCall')
            ->withConsecutive(...$arguments)
            ->willReturnOnConsecutiveCalls(...$returnValues);

        return $this;
    }

    private function ensureFeatureIsEnabled(): ChecklistSyncCommandTest
    {
        $this->parameterStore
            ->method('getFeatureFlag')
            ->willReturn('1');

        return $this;
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

    private function ensureRestClientReturnsRows(): ChecklistSyncCommandTest
    {
        $this->restClient
            ->expects($this->once())
            ->method('apiCall')
            ->withConsecutive([])
            ->with('get', 'report/all-with-queued-checklists', ['row_limit' => '30'], 'Report\Report[]', [], false)
            ->willReturnOnConsecutiveCalls([
                $this->buildReport(),
                $this->buildReport(),
            ]);

        return $this;
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

    private function ensurePdfGenerationWillSucceed(): ChecklistSyncCommandTest
    {
        $this->pdfGenerator
            ->method('generate')
            ->willReturn('file-contents');

        return $this;
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

    private function assertChecklistsAreFetchedWithLimitOf(string $limit)
    {
        $this->restClient
            ->expects($this->at(0))
            ->method('apiCall')
            ->with('get', 'report/all-with-queued-checklists', ['row_limit' => $limit], 'Report\Report[]', [], false)
            ->willReturn([
                $this->buildReport(),
                $this->buildReport(),
            ]);

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

    private function assertChecklistsAreFetchedWithDefaultLimit()
    {
        $this->restClient
            ->expects($this->at(0))
            ->method('apiCall')
            ->with('get', 'report/all-with-queued-checklists', ['row_limit' => ChecklistSyncCommand::FALLBACK_ROW_LIMITS], 'Report\Report[]', [], false)
            ->willReturn([
                $this->buildReport(),
                $this->buildReport(),
            ]);

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

    private function assertChecklistStatusWillBeUpdatedWithSuccess(): ChecklistSyncCommandTest
    {
        $this->restClient
            ->expects($this->at(1))
            ->method('apiCall')
            ->with(
                'put',
                'checklist/3923',
                json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_SUCCESS,
                ]),
                'raw',
                [],
                false
            );

        return $this;
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

    private function ensureSyncWillFailWith(Throwable $e): ChecklistSyncCommandTest
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
}
