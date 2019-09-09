<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Service\DocumentService;
use AppBundle\Service\File\Storage\FileNotFoundException;
use AppBundle\Service\File\Storage\S3Storage;
use AppBundle\Service\File\Storage\StorageInterface;
use Aws\Command;
use Aws\S3\Exception\S3Exception;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use ZipArchive;

class DocumentZipFileCreatorTest extends TestCase
{
    /**
     * @var DocumentsZipFileCreator
     */
    private $object;

    public function setUp(): void
    {
        $this->storage = m::mock(StorageInterface::class);
        $this->reportSubmission = m::mock(ReportSubmission::class, [
            'getDocuments' => [],
        ]);

        $this->object = new DocumentsZipFileCreator($this->reportSubmission, $this->storage);
    }

    public function testcreateZipFileNoDocuments()
    {
        $this->reportSubmission->shouldReceive('isDownloadable')->once()->withNoArgs()->andReturn(true);
        $this->reportSubmission->shouldReceive('getDocuments')->andReturn([]);

        $this->expectException('RuntimeException');
        $this->object->createZipFile();
    }

    public function testcreateZipFileNotDownloadable()
    {
        $this->reportSubmission->shouldReceive('isDownloadable')->once()->withNoArgs()->andReturn(false);

        $this->expectException('RuntimeException', DocumentsZipFileCreator::MSG_NOT_DOWNLOADABLE);
        $this->object->createZipFile();
    }

    /**
     * @group acs
     */
    public function testCreateZipFile()
    {
        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getZipName()->willReturn('Report_12345678_2017_2018.zip');

        $documentsContents = [
            'file-name1.pdf' => 'doc1 content',
            'file-name2.pdf' => 'doc2 content',
            'file-name3.pdf' => 'doc3 content'
        ];

        $sut = new DocumentsZipFileCreator();

        $fileName = $sut->createZipFileFromDocumentContents($documentsContents, $reportSubmission->reveal());

        self::assertTrue(file_exists($fileName));

        $zip = new ZipArchive();
        $zip->open($fileName);

        self::assertEquals(3, $zip->numFiles);
    }

    /**
     * @group acs
     */
    public function testcreateMultiZipFile()
    {
        $sut = new DocumentsZipFileCreator();

        $zipFileContents = ['some content', 'some different content'];
        $zipFiles = [];

        $zip = new ZipArchive();

        foreach ($zipFileContents as $content) {
            $document = $sut::TMP_ROOT_PATH . "test-" . microtime(1);
            file_put_contents($document, $content);

            $zip->open($document, ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::CHECKCONS);
            $zip->addFile($document, $document);

            $zipFiles[] = $document;

            $zip->close();
        }

        $zippedZipFiles = $sut->createMultiZipFile($zipFiles);

        $zip->open($zippedZipFiles);

        self::assertEquals(2, $zip->numFiles);

        $zip->close();
    }

    public function tearDown(): void
    {
        m::close();
    }
}
