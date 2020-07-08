<?php
namespace App\Tests\Command;

use AppBundle\Command\ChecklistSyncCommand;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use AppBundle\Model\Sirius\QueuedChecklistData;
use AppBundle\Service\ChecklistPdfGenerator;
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
    public function doesNotAttemptToSyncFailedPdfGenerations()
    {
        $this
            ->ensureFeatureIsEnabled()
            ->ensureRestClientReturnsRows()
            ->ensurePdfGenerationWillFail()
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

    private function ensurePdfGenerationWillFail(): ChecklistSyncCommandTest
    {
        $this->pdfGenerator
            ->method('generate')
            ->willReturn(ChecklistPdfGenerator::FAILED_TO_GENERATE);

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
            ->method('apiCall')
            ->with('get', 'report/all-with-queued-checklists', ['row_limit' => '100'], 'Report\Report[]', [], false)
            ->willReturn([
                $this->buildReport(),
                $this->buildReport()
            ]);

        return $this;
    }

    private function assertChecklistsAreFetchedWithLimitOf(string $limit)
    {
        $this->restClient
            ->expects($this->once())
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
            ->expects($this->once())
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
