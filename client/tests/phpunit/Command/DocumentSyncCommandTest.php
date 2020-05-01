<?php
namespace App\Tests\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Model\Sirius\QueuedDocumentData;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\DocumentSyncService;
use AppBundle\Service\FeatureFlagService;
use DateTime;
use DateTimeZone;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DocumentSyncCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $rawQueuedDocumentData = json_encode([
            [
                'document_id' => 6789,
                'report_submission_id' => 1234,
                'ndr_id' => 1234,
                'case_number' => '1234abc',
                'is_report_pdf' => true,
                'filename' => 'test.pdf',
                'storage_reference' => 'stor-ref-123',
                'report_start_date' => '2017-02-01',
                'report_end_date' => '2018-01-31',
                'report_submit_date' => '2020-04-29 15:05:23',
                'report_type' => '104'
            ]
        ]);

        $queuedDocumentData = (new QueuedDocumentData())
            ->setDocumentId(6789)
            ->setReportSubmissionId(1234)
            ->setNdrId(1234)
            ->setCaseNumber('1234abc')
            ->setIsReportPdf(true)
            ->setFilename('test.pdf')
            ->setStorageReference('stor-ref-123')
            ->setReportStartDate(new DateTime('2017-02-01', new DateTimeZone('Europe/London')))
            ->setReportEndDate(new DateTime('2018-01-31', new DateTimeZone('Europe/London')))
            ->setReportSubmitDate(new DateTime('2020-04-29 15:05:23', new DateTimeZone('Europe/London')))
            ->setReportType('104');

        /** @var FeatureFlagService|ObjectProphecy $featureFlags */
        $featureFlags = self::prophesize(FeatureFlagService::class);
        $featureFlags
            ->get(FeatureFlagService::FLAG_DOCUMENT_SYNC)
            ->shouldBeCalled()
            ->willReturn('1');

        /** @var RestClient|ObjectProphecy $restClient */
        $restClient = self::prophesize(RestClient::class);
        $restClient
            ->apiCall('get', 'document/queued', [], 'array', Argument::type('array'), false)
            ->shouldBeCalled()
            ->willReturn($rawQueuedDocumentData);

        /** @var DocumentSyncService|ObjectProphecy $documentSyncService */
        $documentSyncService = self::prophesize(DocumentSyncService::class);
        $documentSyncService
            ->syncDocument($queuedDocumentData)
            ->shouldBeCalled();

        $kernel = static::bootKernel([ 'debug' => false ]);
        $application = new Application($kernel);

        /** @var ContainerInterface */
        $container = $kernel->getContainer();
        $container->set(DocumentSyncService::class, $documentSyncService->reveal());
        $container->set(RestClient::class, $restClient->reveal());
        $container->set(FeatureFlagService::class, $featureFlags->reveal());

        $command = $application->find('digideps:document-sync');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('1 documents to upload', $output);
    }

    public function testSleepsWhenTurnedOff()
    {
        /** @var FeatureFlagService|ObjectProphecy $featureFlags */
        $featureFlags = self::prophesize(FeatureFlagService::class);
        $featureFlags
            ->get(FeatureFlagService::FLAG_DOCUMENT_SYNC)
            ->shouldBeCalled()
            ->willReturn('0');

        /** @var RestClient|ObjectProphecy $restClient */
        $restClient = self::prophesize(RestClient::class);
        $restClient
            ->apiCall(Argument::cetera())
            ->shouldNotBeCalled();

        /** @var DocumentSyncService|ObjectProphecy $documentSyncService */
        $documentSyncService = self::prophesize(DocumentSyncService::class);
        $documentSyncService
            ->syncDocument(Argument::cetera())
            ->shouldNotBeCalled();

        $kernel = static::bootKernel([ 'debug' => false ]);
        $application = new Application($kernel);

        /** @var ContainerInterface */
        $container = $kernel->getContainer();
        $container->set(DocumentSyncService::class, $documentSyncService->reveal());
        $container->set(RestClient::class, $restClient->reveal());
        $container->set(FeatureFlagService::class, $featureFlags->reveal());

        $command = $application->find('digideps:document-sync');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Feature disabled, sleeping', $output);
    }
}
