<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Report\Document;
use Symfony\Bridge\Doctrine\Tests\Fixtures\User;
use Tests\AppBundle\Controller\AbstractTestController;

class DocumentControllerTest extends AbstractTestController
{
    // users
    private static $tokenDeputy;

    // lay
    private static $deputy1;
    private static $client1;

    private static $report1;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);

        // report 1
        self::$report1 = self::fixtures()->createReport(self::$client1);

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
        self::$tokenDeputy = $this->loginAsDeputy();
    }

    public function testAddDocumentForDeputy()
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId . '/document';

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
        $this->assertEquals(self::$deputy1->getId() , $document->getCreatedBy()->getId());
        $this->assertInstanceof(\DateTime::class, $document->getCreatedOn());
        $this->assertEquals('s3StorageKey', $document->getStorageReference());
        $this->assertEquals('testfile.pdf', $document->getFilename());
        $this->assertEquals(true, $document->isIsReportPdf());
    }

    /**
     * @depends testAddDocumentForDeputy
     */
    public function testgetSoftDeletedDocuments()
    {
        $repo = self::fixtures()->getRepo('Report\Document');
        // add other two documents
        $d1 = (new Document(self::$report1))
            ->setFileName('file1.pdf')->setStorageReference('sr1')
            ->setReport(null); // failing at flush time, not clear why
        $d2 = (new Document(self::$report1))
            ->setFileName('file2.pdf')->setStorageReference('sr2')
            ->setReport(null); // failing at flush time, not clear why
        self::fixtures()->persist($d1, $d2)->flush();
        $this->assertCount(3, $repo->findAll());
        //delete one
        self::fixtures()->remove($d1)->flush()->clear();
        $this->assertCount(2, $repo->findAll());

        $url = '/document/soft-deleted';

        $this->assertJsonRequest('GET', $url, [
            'mustFail' => true,
            'ClientSecret' => '123abc-deputy',
        ]);

        $records = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'ClientSecret' => '123abc-admin',
        ])['data'];

        $this->assertCount(3, $records);
        $this->assertNotEmpty($records[0]['id']);
        $this->assertNotEmpty($records[0]['storage_reference']);
        $this->assertNotEmpty($records[1]['id']);
        $this->assertNotEmpty($records[1]['storage_reference']);
        $this->assertNotEmpty($records[2]['id']);
        $this->assertNotEmpty($records[2]['storage_reference']);

    }
}
