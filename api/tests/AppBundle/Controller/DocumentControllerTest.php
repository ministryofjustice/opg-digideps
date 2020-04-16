<?php declare(strict_types=1);

namespace Tests\AppBundle\Controller;


use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Repository\DocumentRepository;
use DateTime;


class DocumentControllerTest extends AbstractTestController
{
    /** @var DocumentRepository */
    private $repo;

    // users
    private static $tokenDeputy;

    // lay
    private static $deputy1;
    private static $client1;

    private static $report1;
    private static $ndr1;

    /** @var Document */
    private static $document;

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
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);

        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$ndr1 = self::fixtures()->createNdr(self::$client1);

        self::$document = self::fixtures()->createDocument(self::$report1, 'file_name.pdf');

        self::fixtures()->flush();

        $this->repo = self::fixtures()->getRepo('Report\Document');
        self::$tokenDeputy = $this->loginAsDeputy();
    }

    public function testAddDocumentForDeputy()
    {
        $type = 'report';
        $reportId = self::$report1->getId();
        $url = "/document/{$type}/{$reportId}";

        // assert Auth
        $this->assertEndpointNeedsAuth('POST', $url);

        // assert POST for deputy
        $data = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'file_name'=> 'testfile.pdf',
                'storage_reference'   => 's3StorageKey',
                'is_report_pdf'   => true
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

    public function testAddDocumentNdr()
    {
        $type = 'ndr';
        $reportId = self::$ndr1->getId();
        $url = "/document/{$type}/{$reportId}";

        $data = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'file_name'=> 'ndr.pdf',
                'storage_reference'   => 's3NdrStorageKey',
                'is_report_pdf'   => true
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

    public function testGetQueuedDocumentsUsesSecretAuth(): void
    {
        $return = $this->assertJsonRequest('GET', '/document/queued', [
            'mustFail' => true,
            'ClientSecret' => 'WRONG CLIENT SECRET',
            'assertCode' => 403,
            'assertResponseCode' => 403,
        ]);

        $this->assertStringContainsString('client secret not accepted', $return['message']);

        $return = $this->assertJsonRequest('GET', '/document/queued', [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        self::assertCount(0, $return['data']);
    }

    public function testGetQueuedDocuments(): void
    {
        // Queue a document
        $document = $this->repo->find(self::$document->getId());
        self::assertInstanceOf(Document::class, $document);

        $document->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
        self::fixtures()->flush();

        $return = $this->assertJsonRequest('GET', '/document/queued', [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        self::assertCount(1, $return['data']);
    }

    public function testUpdateDocument_sync_success(): void
    {
        $url = sprintf('/document/%s', self::$document->getId());

        $syncTime = new DateTime();

        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['syncStatus' => Document::SYNC_STATUS_SUCCESS]
        ]);

        self::assertEquals(self::$document->getId(), $response['data']['id']);
        self::assertEquals(Document::SYNC_STATUS_SUCCESS, $response['data']['synchronisation_status']);
        self::assertEqualsWithDelta($syncTime->getTimeStamp(), (new Datetime($response['data']['synchronisation_time']))->getTimestamp(), 5);
    }

    /**
     * @dataProvider statusProvider
     */
    public function testUpdateDocument_not_success(string $status, ?string $error): void
    {
        $url = sprintf('/document/%s', self::$document->getId());

        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['syncStatus' => $status, 'syncError' => $error]
        ]);

        self::assertEquals(self::$document->getId(), $response['data']['id']);
        self::assertEquals($status, $response['data']['synchronisation_status']);
        self::assertEquals($error, $response['data']['synchronisation_error']);
    }

    public function statusProvider()
    {
        return [
            'Permanent error' => [Document::SYNC_STATUS_PERMANENT_ERROR, 'Permanent error occurred'],
            'Temporary error' => [Document::SYNC_STATUS_TEMPORARY_ERROR, 'Temporary error occurred'],
            'In progress' => [Document::SYNC_STATUS_IN_PROGRESS, null],
            'Queued' => [Document::SYNC_STATUS_QUEUED, null],
        ];
    }
}
