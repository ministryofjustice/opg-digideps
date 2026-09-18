<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\File;

use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\File\FileNameManipulation;
use OPG\Digideps\Frontend\Service\File\ImageConvertor;
use OPG\Digideps\Frontend\Service\File\MimeTypeAndExtensionChecker;
use OPG\Digideps\Frontend\Service\File\S3FileUploader;
use OPG\Digideps\Frontend\Service\File\Storage\S3Storage;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\DocumentHelpers;
use OPG\Digideps\Frontend\TestHelpers\ReportHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function PHPUnit\Framework\isInstanceOf;

class S3FileUploaderTest extends KernelTestCase
{
    private string $projectDir;
    private S3Storage&MockObject $storage;
    private RestClient&MockObject $restClient;
    private FileNameManipulation&MockObject $fileNameFixer;
    private DateTimeProvider&MockObject $dateTimeProvider;
    private MimeTypeAndExtensionChecker&MockObject $mimeTypeAndExtensionChecker;
    private ImageConvertor&MockObject $imageConvertor;
    private S3FileUploader $sut;

    public function setUp(): void
    {
        $this->projectDir = self::bootKernel()->getProjectDir();

        $this->storage = self::createMock(S3Storage::class);
        $this->restClient = self::createMock(RestClient::class);
        $this->fileNameFixer = self::createMock(FileNameManipulation::class);
        $this->dateTimeProvider = self::createMock(DateTimeProvider::class);
        $this->mimeTypeAndExtensionChecker = self::createMock(MimeTypeAndExtensionChecker::class);
        $this->imageConvertor = self::createMock(ImageConvertor::class);

        $this->sut = new S3FileUploader(
            $this->storage,
            $this->restClient,
            $this->fileNameFixer,
            $this->dateTimeProvider,
            $this->mimeTypeAndExtensionChecker,
            $this->imageConvertor
        );
    }

    public function testUploadFileAndPersistDocument(): void
    {
        $fileName = 'dd_fileuploadertest.pdf';
        $fileContent = 'testcontent';
        $now = new \DateTime();
        $report = ReportHelpers::createReport();
        $expectedStorageRef = sprintf('dd_doc_%s_%s%s', $report->getId(), $now->format('U'), $now->format('v'));

        $this->dateTimeProvider->method('getDateTime')->willReturn($now);

        $this->storage->expects(self::once())
            ->method('store')
            ->with($expectedStorageRef, $fileContent);

        $this->restClient->expects(self::once())
            ->method('post')
            ->with('/document/report/1', isInstanceOf(Document::class), ['document']);

        $doc = $this->sut->uploadFileAndPersistDocument($report, $fileContent, $fileName, false);

        self::assertStringMatchesFormat($expectedStorageRef, $doc->getStorageReference());
        self::assertEquals($fileName, $doc->getFileName());
        self::assertFalse($doc->isReportPdf());
    }

    public function testUploadSupportingFilesAndPersistDocumentsSingleFile(): void
    {
        $filePath = sprintf('%s/tests/Unit/TestData/good-jpeg', $this->projectDir);
        $uploadedFile = new UploadedFile($filePath, 'good-jpeg.jpeg', 'image/jpeg');

        $report = ReportHelpers::createReport();
        $now = new \DateTime();

        $this->fileNameFixer->expects(self::once())
            ->method('addMissingFileExtension')
            ->with($uploadedFile)
            ->willReturn('good-jpeg.jpeg');

        $this->imageConvertor->expects(self::once())
            ->method('convert')
            ->with('good_jpeg.jpeg', self::anything())
            ->willReturn(['body content', 'good_jpeg.jpeg']);

        $this->mimeTypeAndExtensionChecker->expects(self::once())
            ->method('check')
            ->willReturn(true);

        $this->dateTimeProvider->method('getDateTime')->willReturn($now);

        $this->storage->expects(self::once())->method('store');
        $this->restClient->expects(self::once())->method('post');

        $files = [$uploadedFile];

        $this->sut->uploadSupportingFilesAndPersistDocuments($files, $report);
    }

    public function testUploadSupportingFilesAndPersistDocumentsMultipleFiles(): void
    {
        $jpeg = new UploadedFile(sprintf('%s/tests/Unit/TestData/good-jpeg', $this->projectDir), 'good-jpeg');
        $png = new UploadedFile(sprintf('%s/tests/Unit/TestData/good-png.png', $this->projectDir), 'good-png.png');
        $pdf = new UploadedFile(sprintf('%s/tests/Unit/TestData/good-pdf.pdf', $this->projectDir), 'good-pdf.pdf');
        $heic = new UploadedFile(sprintf('%s/tests/Unit/TestData/good-heic.heic', $this->projectDir), 'good-heic.heic');
        $jfif = new UploadedFile(sprintf('%s/tests/Unit/TestData/good-jfif.jfif', $this->projectDir), 'good-jfif.jfif');
        $files = [$jpeg, $png, $pdf, $heic, $jfif];
        $numFiles = count($files);

        $report = ReportHelpers::createReport();
        $now = new \DateTime();

        $this->fileNameFixer->expects(self::exactly($numFiles))
            ->method('addMissingFileExtension')
            ->willReturn('the-fixed-file-name');

        $this->imageConvertor->expects(self::exactly($numFiles))
            ->method('convert')
            ->with('the_fixed_file_name', self::anything())
            ->willReturn(['body content', 'the-fixed-file-name']);

        $this->mimeTypeAndExtensionChecker->expects(self::exactly($numFiles))
            ->method('check')
            ->willReturn(true);

        $this->dateTimeProvider->method('getDateTime')->willReturn($now);

        $this->storage->expects(self::exactly($numFiles))->method('store');
        $this->restClient->expects(self::exactly($numFiles))->method('post');

        $this->sut->uploadSupportingFilesAndPersistDocuments($files, $report);
    }

    public function testRemoveFileFromS3(): void
    {
        $reportPdf = DocumentHelpers::createReportPdfDocument();

        $this->storage->expects(self::once())->method('removeFromS3')->with($reportPdf->getStorageReference());

        $this->sut->removeFileFromS3($reportPdf);
    }

    public function testRemoveFileFromS3MissingStorageRef(): void
    {
        self::expectException(\Exception::class);

        $reportPdf = DocumentHelpers::createReportPdfDocument();
        $reportPdf->setStorageReference(null);

        $this->storage->expects(self::never())->method('removeFromS3');

        $this->sut->removeFileFromS3($reportPdf);
    }
}
