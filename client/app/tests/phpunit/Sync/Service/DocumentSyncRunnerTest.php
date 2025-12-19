<?php

namespace DigidepsTests\Sync\Service;

use App\Serializer\SiriusDocumentUploadNormalizer;
use App\Service\Client\RestClient;
use App\Sync\Model\Sirius\QueuedDocumentData;
use App\Sync\Service\DocumentSyncRunner;
use App\Sync\Service\DocumentSyncService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Serializer\SerializerInterface;

class DocumentSyncRunnerTest extends KernelTestCase
{
    private DocumentSyncService $syncService;
    private RestClient $restClient;
    private DocumentSyncRunner $sut;

    public function setUp(): void
    {
        /** @var SerializerInterface $serializer */
        $serializer = self::bootKernel()->getContainer()->get('serializer');

        $this->syncService = self::createMock(DocumentSyncService::class);
        $this->restClient = self::createMock(RestClient::class);

        $this->sut = new DocumentSyncRunner($this->syncService, $this->restClient, $serializer);
    }

    public function testRun(): void
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
                'report_type' => '104',
            ],
        ]);

        $queuedDocumentData = (new QueuedDocumentData())
            ->setDocumentId(6789)
            ->setReportSubmissionId(1234)
            ->setNdrId(1234)
            ->setCaseNumber('1234abc')
            ->setIsReportPdf(true)
            ->setFilename('test.pdf')
            ->setStorageReference('stor-ref-123')
            ->setReportStartDate(new \DateTime('2017-02-01', new \DateTimeZone('Europe/London')))
            ->setReportEndDate(new \DateTime('2018-01-31', new \DateTimeZone('Europe/London')))
            ->setReportSubmitDate(new \DateTime('2020-04-29 15:05:23', new \DateTimeZone('Europe/London')))
            ->setReportType('104');

        $this->restClient
            ->expects(self::once())
            ->method('apiCall')
            ->with('get', 'document/queued', ['row_limit' => '100'], 'array', self::isType('array'), false)
            ->willReturn($rawQueuedDocumentData);

        $this->syncService
            ->expects(self::once())
            ->method('syncDocument')
            ->with($queuedDocumentData);

        $this->syncService
            ->expects(self::once())
            ->method('getDocsNotSyncedCount')
            ->willReturn(0);

        $this->syncService
            ->expects(self::once())
            ->method('getSyncErrorSubmissionIds')
            ->willReturn([]);

        $output = new BufferedOutput();

        $this->sut->run($output, 100);

        $content = $output->fetch();

        $this->assertStringContainsString('1 documents to upload', $content);
        $this->assertStringContainsString('sync_documents_to_sirius - success - Sync command completed', $content);
    }

    public function testExecuteWithSyncErrorSubmissionIds(): void
    {
        $this->restClient
            ->expects(self::once())
            ->method('apiCall')
            ->with('get', 'document/queued', ['row_limit' => '100'], 'array', self::isType('array'), false)
            ->willReturn(json_encode([]));

        $this->syncService
            ->expects(self::once())
            ->method('getSyncErrorSubmissionIds')
            ->willReturn([1]);

        $this->syncService
            ->expects(self::once())
            ->method('setSubmissionsDocumentsToPermanentError');

        $this->syncService
            ->expects(self::once())
            ->method('getDocsNotSyncedCount')
            ->willReturn(6);

        $this->syncService
            ->expects(self::once())
            ->method('setSyncErrorSubmissionIds')
            ->with([]);

        $this->syncService
            ->expects(self::once())
            ->method('setDocsNotSyncedCount')
            ->with(0);

        $output = new BufferedOutput();

        $this->sut->run($output, 100);

        $content = $output->fetch();

        $this->assertStringContainsString('0 documents to upload', $content);
        $this->assertStringContainsString('sync_documents_to_sirius - success - 6 documents remaining to sync', $content);
    }
}
