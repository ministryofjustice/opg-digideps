<?php

declare(strict_types=1);

namespace App\Service\File;

use App\Entity\Report\ReportSubmission;
use App\Model\RetrievedDocument;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ZipArchive;

class DocumentZipFileCreatorTest extends TestCase
{
    use ProphecyTrait;

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
        $expectedRetrievedDoc2->setFileName('file-name2.pdf');
        $expectedRetrievedDoc2->setContent('doc2 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission1->reveal());

        $expectedRetrievedDoc3 = new RetrievedDocument();
        $expectedRetrievedDoc3->setFileName('file-name3.pdf');
        $expectedRetrievedDoc3->setContent('doc3 contents');
        $expectedRetrievedDoc3->setReportSubmission($reportSubmission2->reveal());

        $retrievedDocuments = [$expectedRetrievedDoc1, $expectedRetrievedDoc2, $expectedRetrievedDoc3];

        $sut = new DocumentsZipFileCreator();

        $expectedZipFilenames = ['/tmp/zip-file-1.zip', '/tmp/zip-file-2.zip'];
        $actualZipFileNames = $sut->createZipFilesFromRetrievedDocuments($retrievedDocuments);

        $zip = new ZipArchive();

        foreach ($expectedZipFilenames as $zipFileName) {
            self::assertContains($zipFileName, $actualZipFileNames);
            self::assertTrue(file_exists($zipFileName));
        }

        $zip->open('/tmp/zip-file-1.zip');
        self::assertIsInt($zip->locateName('file-name1.pdf'));
        self::assertIsInt($zip->locateName('file-name2.pdf'));

        $zip->open('/tmp/zip-file-2.zip');
        self::assertIsInt($zip->locateName('file-name3.pdf'));

        $zip->close();
    }

    public function testCreateMultiZipFile()
    {
        $sut = new DocumentsZipFileCreator();

        $zip = new ZipArchive();
        $zipFileContents = ['zip1' => 'some content', 'zip2' => 'some different content'];
        $zipFiles = self::generateTestZipFiles($zip, $zipFileContents);

        $zippedZipFiles = $sut->createMultiZipFile($zipFiles);

        $zip->open($zippedZipFiles);

        self::assertEquals(2, $zip->numFiles);
        self::assertIsInt($zip->locateName('zip1'));
        self::assertIsInt($zip->locateName('zip2'));

        $zip->close();
    }

    protected function generateTestZipFiles(ZipArchive $zip, array $zipFileContent)
    {
        $zipFiles = [];

        foreach ($zipFileContent as $fileName => $content) {
            $zipFile = "/tmp/${fileName}";
            file_put_contents($zipFile, $content);

            $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::CHECKCONS);
            $zip->addFile($zipFile, $zipFile);

            $zipFiles[] = $zipFile;

            $zip->close();
        }

        return $zipFiles;
    }
}
