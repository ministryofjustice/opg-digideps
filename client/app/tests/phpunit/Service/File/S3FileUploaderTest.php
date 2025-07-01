<?php

declare(strict_types=1);

namespace App\Service\File;

use App\Entity\Report\Document;
use App\Service\Client\RestClient;
use App\Service\File\Storage\S3Storage;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\DocumentHelpers;
use App\TestHelpers\ReportHelpers;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3FileUploaderTest extends KernelTestCase
{
    use ProphecyTrait;

    private string $projectDir;
    private ObjectProphecy $storage;
    private ObjectProphecy $restClient;
    private S3FileUploader $sut;
    private ObjectProphecy $fileNameFixer;
    private ObjectProphecy $dateTimeProvider;
    private ObjectProphecy $mimeTypeAndExtensionChecker;
    private ObjectProphecy|ImageConvertor $imageConvertor;

    public function setUp(): void
    {
        $this->projectDir = self::bootKernel()->getProjectDir();

        $this->storage = self::prophesize(S3Storage::class);
        $this->restClient = self::prophesize(RestClient::class);
        $this->fileNameFixer = self::prophesize(FileNameFixer::class);
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->mimeTypeAndExtensionChecker = self::prophesize(MimeTypeAndExtensionChecker::class);
        $this->imageConvertor = self::prophesize(ImageConvertor::class);

        $this->sut = new S3FileUploader(
            $this->storage->reveal(),
            $this->restClient->reveal(),
            $this->fileNameFixer->reveal(),
            $this->dateTimeProvider->reveal(),
            $this->mimeTypeAndExtensionChecker->reveal(),
            $this->imageConvertor->reveal()
        );
    }

    /** @test */
    public function uploadFileAndPersistDocument()
    {
        $fileName = 'dd_fileuploadertest.pdf';
        $fileContent = 'testcontent';
        $now = new \DateTime();
        $report = ReportHelpers::createReport();
        $expectedStorageRef = sprintf('dd_doc_%s_%s%s', $report->getId(), $now->format('U'), $now->format('v'));

        $this->dateTimeProvider->getDateTime()->willReturn($now);
        $this->storage->store($expectedStorageRef, $fileContent)->shouldBeCalled();

        $this->restClient
            ->post('/document/report/1', Argument::type(Document::class), ['document'])
            ->shouldBeCalled();

        /* @var $document Document */
        $doc = $this->sut->uploadFileAndPersistDocument($report, $fileContent, $fileName, false);

        $this->assertStringMatchesFormat($expectedStorageRef, $doc->getStorageReference());
        $this->assertEquals($fileName, $doc->getFileName());
        $this->assertEquals(false, $doc->isReportPdf());
    }

    /** @test */
    public function uploadSupportingFilesAndPersistDocumentsSingleFile()
    {
        $filePath = sprintf('%s/tests/phpunit/TestData/good-jpeg', $this->projectDir);
        $uploadedFile = new UploadedFile($filePath, 'good-jpeg.jpeg', 'image/jpeg');

        $report = ReportHelpers::createReport();
        $now = new \DateTime();

        $this->fileNameFixer->lowerCaseFileExtension($uploadedFile)->shouldBeCalled()->willReturn($uploadedFile);
        $this->fileNameFixer->addMissingFileExtension($uploadedFile)->shouldBeCalled()->willReturn('good-jpeg.jpeg');
        $this->fileNameFixer->removeWhiteSpaceBeforeFileExtension('good-jpeg.jpeg')->shouldBeCalled()->willReturn('good-jpeg.jpeg');
        $this->fileNameFixer->removeUnusualCharacters('good-jpeg.jpeg')->shouldBeCalled()->willReturn('good_jpeg.jpeg');
        $this->imageConvertor->convert('good_jpeg.jpeg', Argument::any())->shouldBeCalled()->willReturn(['body content', 'good_jpeg.jpeg']);

        $this->mimeTypeAndExtensionChecker->check(Argument::cetera())->shouldBeCalled()->willReturn(true);

        $this->dateTimeProvider->getDateTime()->willReturn($now);
        $this->storage->store(Argument::cetera())->shouldBeCalled();
        $this->restClient->post(Argument::cetera())->shouldBeCalled();

        $files = [$uploadedFile];

        $this->sut->uploadSupportingFilesAndPersistDocuments($files, $report);
    }

    /** @test */
    public function uploadSupportingFilesAndPersistDocumentsMultipleFiles()
    {
        $jpeg = new UploadedFile(sprintf('%s/tests/phpunit/TestData/good-jpeg', $this->projectDir), 'good-jpeg');
        $png = new UploadedFile(sprintf('%s/tests/phpunit/TestData/good-png.png', $this->projectDir), 'good-png.png');
        $pdf = new UploadedFile(sprintf('%s/tests/phpunit/TestData/good-pdf.pdf', $this->projectDir), 'good-pdf.pdf');
        $heic = new UploadedFile(sprintf('%s/tests/phpunit/TestData/good-heic.heic', $this->projectDir), 'good-heic.heic');
        $jfif = new UploadedFile(sprintf('%s/tests/phpunit/TestData/good-jfif.jfif', $this->projectDir), 'good-jfif.jfif');
        $files = [$jpeg, $png, $pdf, $heic, $jfif];

        $report = ReportHelpers::createReport();
        $now = new \DateTime();

        $this->fileNameFixer->lowerCaseFileExtension(Argument::cetera())->shouldBeCalledTimes(5)->shouldBeCalled()->willReturn($jpeg);
        $this->fileNameFixer->addMissingFileExtension(Argument::cetera())->shouldBeCalledTimes(5)->willReturn('the-fixed-file-name');
        $this->fileNameFixer->removeWhiteSpaceBeforeFileExtension('the-fixed-file-name')->shouldBeCalledTimes(5)->willReturn('the-fixed-file-name');
        $this->fileNameFixer->removeUnusualCharacters('the-fixed-file-name')->shouldBeCalledTimes(5)->willReturn('the_fixed_file_name');
        $this->imageConvertor->convert('the_fixed_file_name', Argument::any())->shouldBeCalledTimes(5)->willReturn(['body content', 'the-fixed-file-name']);

        $this->mimeTypeAndExtensionChecker->check(Argument::cetera())->shouldBeCalledTimes(5)->willReturn(true);

        $this->dateTimeProvider->getDateTime()->willReturn($now);
        $this->storage->store(Argument::cetera())->shouldBeCalledTimes(5);
        $this->restClient->post(Argument::cetera())->shouldBeCalledTimes(5);

        $this->sut->uploadSupportingFilesAndPersistDocuments($files, $report);
    }

    /** @test */
    public function removeFileFromS3()
    {
        $reportPdf = DocumentHelpers::createReportPdfDocument();

        $this->storage->removeFromS3($reportPdf->getStorageReference())->shouldBeCalled();

        $this->sut->removeFileFromS3($reportPdf);
    }

    /** @test */
    public function removeFileFromS3MissingStorageRef()
    {
        self::expectException(\Exception::class);

        $reportPdf = DocumentHelpers::createReportPdfDocument();
        $reportPdf->setStorageReference(null);

        $this->storage->removeFromS3($reportPdf->getStorageReference())->shouldNotBeCalled();

        $this->sut->removeFileFromS3($reportPdf);
    }
}
