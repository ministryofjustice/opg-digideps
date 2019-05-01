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
}
