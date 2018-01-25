<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Repository\DocumentRepository;
use Symfony\Bridge\Doctrine\Tests\Fixtures\User;

class DocumentControllerTest extends AbstractTestController
{
    /**
     * @var DocumentRepository
     */
    private $repo;

    // users
    private static $tokenDeputy;

    // lay
    private static $deputy1;
    private static $client1;

    private static $report1;
    private static $ndr1;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);

        // report 1
        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$ndr1 = self::fixtures()->createNdr(self::$client1);

        self::fixtures()->flush();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function setup()
    {
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
        $this->assertEquals(true, $document->isIsReportPdf());

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

        self::fixtures()->remove($document)->flush();
        $this->assertJsonRequest('DELETE', '/document/hard-delete/' . $data['id'], [
            'mustSucceed' => true,
            'ClientSecret' => '123abc-admin',
        ]);
    }

    /**
     * @depends testAddDocumentForDeputy
     */
    public function testgetSoftDeletedDocuments()
    {
        $this->assertCount(1, $this->repo->findAll()); // only testfile.pdf
        // add d1 and d2, and soft-delete them
        $d1 = (new Document(self::$report1))
            ->setFileName('file1.pdf')->setStorageReference('sr1')
            ->setReport(null); // failing at flush time, not clear why
        $d2 = (new Document(self::$report1))
            ->setFileName('file2.pdf')->setStorageReference('sr2')
            ->setReport(null); // failing at flush time, not clear why
        self::fixtures()->persist($d1, $d2)->flush();
        $this->assertCount(3, $this->repo->findAll());
        self::fixtures()->remove($d1, $d2)->flush()->clear();
        $this->assertCount(1, $this->repo->findAll()); // only testfile.pdf

        $this->assertJsonRequest('GET', '/document/soft-deleted', [
            'mustFail' => true,
            'ClientSecret' => '123abc-deputy',
        ]);

        $records = $this->assertJsonRequest('GET', '/document/soft-deleted', [
            'mustSucceed' => true,
            'ClientSecret' => '123abc-admin',
        ])['data'];

        $this->assertCount(2, $records);
        $this->assertNotEmpty($records[0]['id']);
        $this->assertEquals('sr1', $records[0]['storage_reference']);
        $this->assertNotEmpty($records[1]['id']);
        $this->assertEquals('sr2', $records[1]['storage_reference']);

        return $d2->getId();
    }

    /**
     * @depends testgetSoftDeletedDocuments
     */
    public function testHardDelete($d2Id)
    {
        // hard delete document1
        $this->assertJsonRequest('DELETE', '/document/hard-delete/' . $d2Id, [
            'mustFail' => true,
            'ClientSecret' => '123abc-deputy',
        ]);
        $this->assertJsonRequest('DELETE', '/document/hard-delete/' . $d2Id, [
            'mustSucceed' => true,
            'ClientSecret' => '123abc-admin',
        ]);

        // assert one got deleted
        $records = $this->assertJsonRequest('GET', '/document/soft-deleted', [
            'mustSucceed' => true,
            'ClientSecret' => '123abc-admin',
        ])['data'];

        $this->assertCount(1, $records);
        $this->assertNotEmpty($records[0]['id']);
        $this->assertEquals('sr1', $records[0]['storage_reference']);
    }

    /**
     * @depends testAddDocumentForDeputy
     */
    public function testHardDeleteFailOnNonSoftDeleteDocument($existingDoocId)
    {
        $this->assertJsonRequest('DELETE', '/document/hard-delete/' . $existingDoocId, [
            'mustFail' => true,
            'ClientSecret' => '123abc-admin',
        ]);

        $this->repo->clear();
        $this->assertInstanceOf(Document::class, $this->repo->find($existingDoocId));
    }
}
