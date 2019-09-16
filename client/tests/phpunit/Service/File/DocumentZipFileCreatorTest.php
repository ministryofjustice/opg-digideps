<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Model\RetrievedDocument;
use AppBundle\Service\File\Storage\StorageInterface;
use Mockery as m;
use Prophecy\Prophecy\ObjectProphecy;
use ZipArchive;

class DocumentZipFileCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentsZipFileCreator
     */
    private $object;

    public function setUp()
    {
        $this->storage = m::mock(StorageInterface::class);
        $this->reportSubmission = m::mock(ReportSubmission::class, [
            'getDocuments' => [],
        ]);

        $this->object = new DocumentsZipFileCreator($this->reportSubmission, $this->storage);
    }

    /**
     * @group acss
     */
    public function testCreateZipFilesFromRetrievedDocuments()
    {
        /** @var ObjectProphecy|ReportSubmission $reportSubmission1 */
        $reportSubmission1 = self::prophesize(ReportSubmission::class);
        $reportSubmission1->getZipName()->shouldBeCalled()->willReturn('zip-file-1.zip');

        /** @var ObjectProphecy|ReportSubmission $reportSubmission2 */
        $reportSubmission2 = self::prophesize(ReportSubmission::class);
        $reportSubmission2->getZipName()->shouldBeCalled()->willReturn('zip-file-2.zip');

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('doc1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission1->reveal());

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name4.pdf');
        $expectedRetrievedDoc2->setContent('doc4 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission2->reveal());

        $retrievedDocuments = [$expectedRetrievedDoc1, $expectedRetrievedDoc2];

        $sut = new DocumentsZipFileCreator();

        $expectedZipFilenames = ['/tmp/zip-file-1.zip', '/tmp/zip-file-2.zip'];
        $actualZipFileNames = $sut->createZipFilesFromRetrievedDocuments($retrievedDocuments);

        $zip = new ZipArchive();

        foreach($expectedZipFilenames as $zipFileName) {
            self::assertContains($zipFileName, $actualZipFileNames);
            self::assertTrue(file_exists($zipFileName));

            $zip->open($zipFileName);
            self::assertEquals(1, $zip->numFiles);
        }

        $zip->open('/tmp/zip-file-1.zip');
        self::assertInternalType('int', $zip->locateName('file-name1.pdf'));

        $zip->open('/tmp/zip-file-2.zip');
        self::assertInternalType('int', $zip->locateName('file-name4.pdf'));

        $zip->close();
    }

    /**
     * @group acss
     */
    public function testCreateMultiZipFile()
    {
        $sut = new DocumentsZipFileCreator();

        $zip = new ZipArchive();
        $zipFileContents = ['zip1' => 'some content', 'zip2' => 'some different content'];
        $zipFiles = self::generateTestZipFiles($zip, $zipFileContents);

        $zippedZipFiles = $sut->createMultiZipFile($zipFiles);

        $zip->open($zippedZipFiles);

        self::assertEquals(2, $zip->numFiles);
        self::assertInternalType('int', $zip->locateName('zip1'));
        self::assertInternalType('int', $zip->locateName('zip1'));

        $zip->close();
    }


    protected function generateTestZipFiles(ZipArchive $zip, array $zipFileContent)
    {
        $zipFiles = [];

        foreach($zipFileContent as $fileName => $content) {
            $zipFile = "/tmp/${fileName}";
            file_put_contents($zipFile, $content);

            $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::CHECKCONS);
            $zip->addFile($zipFile, $zipFile);

            $zipFiles[] = $zipFile;

            $zip->close();
        }

        return $zipFiles;
    }

    public function tearDown()
    {
        m::close();
    }
}
