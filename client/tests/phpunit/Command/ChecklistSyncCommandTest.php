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
            [$this->buildReport(3923)],
            [],
        ];

        $this
            ->ensureFeatureIsEnabled()
            ->assertApiCallsAreMade($apiCallArguments, $returnValues)
            ->ensurePdfGenerationWillFailWith(new PdfGenerationFailedException('Failed to generate PDF'))
            ->invokeTest();
    }

    private function buildReport(int $id)
    {
        $user = (new User())->setEmail('test@test.com');

        $report = (new Report())
            ->setStartDate(new DateTime())
            ->setEndDate(new DateTime())
            ->setReportSubmissions([])
            ->setType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS);

        $checklist = (new Checklist($report))->setSubmittedBy($user);
        $checklist->setId($id);

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

    private function assertApiCallsAreMade(array $argumentArrays, array $returnValueArrays): ChecklistSyncCommandTest
    {
        $this->restClient
            ->expects($this->exactly(count($argumentArrays)))
            ->method('apiCall')
            ->withConsecutive(...$argumentArrays)
            ->willReturnOnConsecutiveCalls(...$returnValueArrays);

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
            [$this->buildReport(3923)],
            [],
        ];

        $this
            ->ensureFeatureIsEnabled()
            ->assertApiCallsAreMade($apiCallArguments, $returnValues)
            ->ensurePdfGenerationWillFailWith(new PdfGenerationFailedException('Failed to generate PDF'))
            ->assertSyncServiceIsNotInvoked()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function fetchesAndSendsQueuedChecklistsToSyncService()
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
                    'syncStatus' => Checklist::SYNC_STATUS_SUCCESS,
                    'uuid' => 'uuid-1',
                ]),
                'raw',
                [],
                false,
            ],
            [
                'put',
                'checklist/3924',
                json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_SUCCESS,
                    'uuid' => 'uuid-2',
                ]),
                'raw',
                [],
                false,
            ],
        ];

        $returnValues = [
            [
                $this->buildReport(3923),
                $this->buildReport(3924),
            ],
            [],
        ];

        $this
            ->ensureFeatureIsEnabled()
            ->assertApiCallsAreMade($apiCallArguments, $returnValues)
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
    public function fetchesAConfigurableLimitOfChecklists()
    {
        $rowLimit = '45';

        $apiCallArguments = [
            [
                'get',
                'report/all-with-queued-checklists',
                ['row_limit' => $rowLimit],
                'Report\Report[]',
                [],
                false,
            ],
        ];

        $returnValues = [
            [],
            [],
        ];

        $this
            ->ensureFeatureIsEnabled()
            ->ensureConfigurableRowLimitIsSetTo($rowLimit)
            ->assertApiCallsAreMade($apiCallArguments, $returnValues)
            ->ensurePdfGenerationWillSucceed()
            ->invokeTest();
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
        $apiCallArguments = [
            [
                'get',
                'report/all-with-queued-checklists',
                ['row_limit' => ChecklistSyncCommand::FALLBACK_ROW_LIMITS],
                'Report\Report[]',
                [],
                false,
            ],
            [
                'put',
                'checklist/3923',
                json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_SUCCESS,
                    'uuid' => 'uuid-1',
                ]),
                'raw',
                [],
                false,
            ],
            [
                'put',
                'checklist/3924',
                json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_SUCCESS,
                    'uuid' => 'uuid-2',
                ]),
                'raw',
                [],
                false,
            ],
        ];

        $returnValues = [
            [$this->buildReport(3923), $this->buildReport(3924)],
            [],
        ];

        $this
            ->ensureFeatureIsEnabled()
            ->ensureConfigurableRowLimitIsNotSet()
            ->ensurePdfGenerationWillSucceed()
            ->assertApiCallsAreMade($apiCallArguments, $returnValues)
            ->assertEachRowWillBeTransformedAndSentToSyncService()
            ->invokeTest();
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
    public function updatesSyncStatusOnFailedDocumentSyncs()
    {
        $apiCallArguments = [
            [
                'get',
                'report/all-with-queued-checklists',
                ['row_limit' => ChecklistSyncCommand::FALLBACK_ROW_LIMITS],
                'Report\Report[]',
                [],
                false,
            ],
            [
                'put',
                'checklist/3923',
                json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_PERMANENT_ERROR,
                    'syncError' => 'Failed to sync document',
                ]),
                'raw',
                [],
                false,
            ],
            [
                'put',
                'checklist/3924',
                json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_PERMANENT_ERROR,
                    'syncError' => 'Failed to sync document',
                ]),
                'raw',
                [],
                false,
            ],
        ];

        $returnValues = [
            [
                $this->buildReport(3923),
                $this->buildReport(3924),
            ],
            [],
        ];

        $this
            ->ensureFeatureIsEnabled()
            ->ensureConfigurableRowLimitIsNotSet()
            ->ensurePdfGenerationWillSucceed()
            ->assertApiCallsAreMade($apiCallArguments, $returnValues)
            ->ensureSyncWillFailWith(new SiriusDocumentSyncFailedException('Failed to sync document'))
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
