<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\Ndr\Ndr;
use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Repository\DocumentRepository;

class DocumentControllerTest extends AbstractTestController
{
    /** @var Report */
    private static $report1;
    private static $report2;

    /** @var Document */
    private static $document1;
    private static $document2;
    private static $document3;

    /** @var DocumentRepository */
    private $repo;

    // users
    private static $tokenDeputy;

    // lay
    private static $deputy1;
    private static $client1;

    /** @var Ndr */
    private static $ndr1;

    /** @var ReportSubmission */
    private static $reportSubmission1;
    private static $reportSubmission2;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function setUp(): void
    {
        parent::setUp();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);

        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$report2 = self::fixtures()->createReport(self::$client1);

        self::$ndr1 = self::fixtures()->createNdr(self::$client1);

        self::$document1 = self::fixtures()->createDocument(self::$report1, 'file_name.pdf');
        self::$document2 = self::fixtures()->createDocument(self::$report1, 'another_file_name.pdf', false);
        self::$document3 = self::fixtures()->createDocument(self::$report2, 'and_another_file_name.pdf');

        self::$reportSubmission1 = self::fixtures()->createReportSubmission(self::$report1);
        self::$reportSubmission2 = self::fixtures()->createReportSubmission(self::$report1);

        self::$document1->setReportSubmission(self::$reportSubmission1);
        self::$document2->setReportSubmission(self::$reportSubmission1);
        self::$document3->setReportSubmission(self::$reportSubmission2);

        self::fixtures()->flush();

        $this->repo = self::fixtures()->getRepo('Report\Document');
        self::$tokenDeputy = $this->loginAsDeputy();
    }

    /** @test */
    public function addDocumentForDeputy()
    {
        $type = 'report';
        $reportId = self::$report1->getId();
        $url = "/document/{$type}/{$reportId}";

        // assert Auth
        $this->assertEndpointNeedsAuth('POST', $url);

        // assert POST for deputy
        $data = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'file_name' => 'testfile.pdf',
                'storage_reference' => 's3StorageKey',
                'is_report_pdf' => true,
            ],
        ])['data'];

        /** @var Document $document */
        $document = self::fixtures()->getRepo('Report\Document')->find($data['id']);

        $this->assertEquals($data['id'], $document->getId());
        $this->assertEquals(self::$deputy1->getId(), $document->getCreatedBy()->getId());
        $this->assertInstanceof(\DateTime::class, $document->getCreatedOn());
        $this->assertEquals('s3StorageKey', $document->getStorageReference());
        $this->assertEquals('testfile.pdf', $document->getFilename());
        $this->assertEquals(true, $document->isReportPdf());

        return $document->getId();
    }

    /** @test */
    public function addDocumentNdr()
    {
        $type = 'ndr';
        $reportId = self::$ndr1->getId();
        $url = "/document/{$type}/{$reportId}";

        $data = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'file_name' => 'ndr.pdf',
                'storage_reference' => 's3NdrStorageKey',
                'is_report_pdf' => true,
            ],
        ])['data'];

        /** @var Document $document */
        $document = $this->repo->find($data['id']);
        $this->assertInstanceOf(Ndr::class, $document->getNdr());

        $this->assertEquals($data['id'], $document->getId());
        $this->assertEquals(self::$deputy1->getId(), $document->getCreatedBy()->getId());
        $this->assertInstanceof(\DateTime::class, $document->getCreatedOn());
        $this->assertEquals('s3NdrStorageKey', $document->getStorageReference());
        $this->assertEquals('ndr.pdf', $document->getFilename());
        $this->assertEquals(true, $document->isReportPdf());

        self::fixtures()->remove($document)->flush();
    }

    /** @test */
    public function getQueuedDocumentsUsesSecretAuth(): void
    {
        $return = $this->assertJsonRequest('GET', '/document/queued', [
            'mustFail' => true,
            'ClientSecret' => 'WRONG CLIENT SECRET',
            'assertCode' => 403,
            'assertResponseCode' => 403,
            'data' => ['row_limit' => 100],
        ]);

        $this->assertStringContainsString('client secret not accepted', $return['message']);

        $return = $this->assertJsonRequest('GET', '/document/queued', [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['row_limit' => 100],
        ]);

        self::assertCount(0, json_decode($return['data'], true));
    }

    /** @test */
    public function getQueuedDocuments(): void
    {
        // Queue a document
        $document = $this->repo->find(self::$document1->getId());
        self::assertInstanceOf(Document::class, $document);

        $document->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
        self::fixtures()->flush();

        $return = $this->assertJsonRequest('GET', '/document/queued', [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['row_limit' => 100],
        ], true);

        self::assertCount(1, json_decode($return['data'], true));
    }

    /** @test */
    public function updateDocumentSyncSuccess(): void
    {
        $url = sprintf('/document/%s', self::$document1->getId());

        $syncTime = new \DateTime();

        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['syncStatus' => Document::SYNC_STATUS_SUCCESS],
        ], true);

        self::assertEquals(self::$document1->getId(), $response['data']['id']);
        self::assertEquals(Document::SYNC_STATUS_SUCCESS, $response['data']['synchronisation_status']);
        self::assertEqualsWithDelta($syncTime->getTimeStamp(), (new \DateTime($response['data']['synchronisation_time']))->getTimestamp(), 5);
    }

    /**
     * @test
     *
     * @dataProvider statusProvider
     */
    public function updateDocumentNotSuccess(string $providedStatus, string $expectedStatus, ?string $error): void
    {
        $url = sprintf('/document/%s', self::$document1->getId());

        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['syncStatus' => $providedStatus, 'syncError' => $error],
        ]);

        self::assertEquals(self::$document1->getId(), $response['data']['id']);
        self::assertEquals($expectedStatus, $response['data']['synchronisation_status']);
        self::assertEquals($error, $response['data']['synchronisation_error']);
    }

    public function statusProvider()
    {
        return [
            'Permanent error' => [Document::SYNC_STATUS_PERMANENT_ERROR, Document::SYNC_STATUS_PERMANENT_ERROR, 'Permanent error occurred'],
            'Temporary error' => [Document::SYNC_STATUS_TEMPORARY_ERROR, Document::SYNC_STATUS_QUEUED, 'Temporary error occurred'],
            'In progress' => [Document::SYNC_STATUS_IN_PROGRESS, Document::SYNC_STATUS_IN_PROGRESS, null],
            'Queued' => [Document::SYNC_STATUS_QUEUED, Document::SYNC_STATUS_QUEUED, null],
        ];
    }

    /**
     * @test
     */
    public function updateDocumentTempErrorsIncreasesSyncAttemptCounterAndSetsToQueued(): void
    {
        $url = sprintf('/document/%s', self::$document1->getId());

        for ($i = 1; $i < 3; ++$i) {
            $response = $this->assertJsonRequest('PUT', $url, [
                'mustSucceed' => true,
                'ClientSecret' => API_TOKEN_DEPUTY,
                'data' => ['syncStatus' => Document::SYNC_STATUS_TEMPORARY_ERROR, 'syncError' => 'Temp error occurred'],
            ]);

            self::assertEquals($i, $response['data']['sync_attempts']);
            self::assertEquals(Document::SYNC_STATUS_QUEUED, $response['data']['synchronisation_status']);
        }
    }

    /**
     * @test
     */
    public function updateDocumentPermErrorReturnsAfter4Attempts(): void
    {
        $document = $this->repo->find(self::$document1->getId());
        self::assertInstanceOf(Document::class, $document);

        $document->incrementSyncAttempts();
        $document->incrementSyncAttempts();
        $document->incrementSyncAttempts();
        $document->incrementSyncAttempts();

        self::fixtures()->flush();

        $url = sprintf('/document/%s', $document->getId());
        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR, 'syncError' => 'Temp error occurred'],
        ]);

        self::assertEquals('Document failed to sync after 4 attempts', $response['data']['synchronisation_error']);
        self::assertEquals(0, $response['data']['sync_attempts']);
    }

    /** @test */
    public function updateRelatedStatusesSuccess(): void
    {
        $response = $this->assertJsonRequest(
            'PUT',
            '/document/update-related-statuses',
            [
                'mustSucceed' => true,
                'ClientSecret' => API_TOKEN_DEPUTY,
                'data' => ['submissionIds' => [self::$reportSubmission1->getId(), self::$reportSubmission2->getId()], 'errorMessage' => 'An error message'],
            ]
        );

        self::assertEquals(1, $response['data']);
    }
}
