<?php declare(strict_types=1);


namespace App\Service\File;

use App\Entity\Report\Document;
use App\Service\Client\RestClient;
use App\Service\File\Storage\S3Storage;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\DocumentHelper;
use App\TestHelpers\ReportHelper;
use DateTime;
use Exception;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3FileUploaderTest extends KernelTestCase
{
    private string $projectDir;
    private ObjectProphecy $storage;
    private ObjectProphecy $restClient;
    private S3FileUploader $sut;
    private ObjectProphecy $fileNameFixer;
    private ObjectProphecy $dateTimeProvider;

    public function setUp(): void
    {
        $this->projectDir = self::bootKernel()->getProjectDir();

        $this->storage = self::prophesize(S3Storage::class);
        $this->restClient = self::prophesize(RestClient::class);
        $this->fileNameFixer = self::prophesize(FileNameFixer::class);
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $this->sut = new S3FileUploader(
            $this->storage->reveal(),
            $this->restClient->reveal(),
            $this->fileNameFixer->reveal(),
            $this->dateTimeProvider->reveal()
        );
    }

    /** @test */
    public function uploadFileAndPersistDocument()
    {
        $fileName = 'dd_fileuploadertest.pdf';
        $fileContent = 'testcontent';
        $now = new DateTime();
        $report = ReportHelper::createReport();
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
    public function uploadSupportingFilesAndPersistDocuments_single_file()
    {
        $filePath = sprintf('%s/tests/phpunit/TestData/good-jpeg', $this->projectDir);
        $fileBody = file_get_contents($filePath);
        $uploadedFile = new UploadedFile($filePath, 'good-jpeg');
        $files = [$uploadedFile];

        $report = ReportHelper::createReport();
        $now = new DateTime();

        $this->fileNameFixer->addMissingFileExtension($uploadedFile, $fileBody)->shouldBeCalled()->willReturn('good-jpeg.jpeg');
        $this->fileNameFixer->removeWhiteSpaceBeforeFileExtension('good-jpeg.jpeg')->shouldBeCalled()->willReturn('good-jpeg.jpeg');
        $this->dateTimeProvider->getDateTime()->willReturn($now);
        $this->storage->store(Argument::cetera())->shouldBeCalled();
        $this->restClient->post(Argument::cetera())->shouldBeCalled();

        $this->sut->uploadSupportingFilesAndPersistDocuments($files, $report);
    }

    /** @test */
    public function uploadSupportingFilesAndPersistDocuments_multiple_files()
    {
        $jpeg = new UploadedFile(sprintf('%s/tests/phpunit/TestData/good-jpeg', $this->projectDir), 'good-jpeg');
        $png = new UploadedFile(sprintf('%s/tests/phpunit/TestData/good-png', $this->projectDir), 'good-png');
        $pdf = new UploadedFile(sprintf('%s/tests/phpunit/TestData/good-pdf', $this->projectDir), 'good-pdf');
        $files = [$jpeg, $png, $pdf];

        $report = ReportHelper::createReport();
        $now = new DateTime();

        $this->fileNameFixer->addMissingFileExtension(Argument::cetera())->shouldBeCalledTimes(3)->willReturn('the-fixed-file-name');
        $this->fileNameFixer->removeWhiteSpaceBeforeFileExtension('the-fixed-file-name')->shouldBeCalledTimes(3)->willReturn('the-fixed-file-name');

        $this->dateTimeProvider->getDateTime()->willReturn($now);
        $this->storage->store(Argument::cetera())->shouldBeCalledTimes(3);
        $this->restClient->post(Argument::cetera())->shouldBeCalledTimes(3);

        $this->sut->uploadSupportingFilesAndPersistDocuments($files, $report);
    }

    /** @test */
    public function removeFileFromS3()
    {
        $reportPdf = DocumentHelper::createReportPdfDocument();

        $this->storage->removeFromS3($reportPdf->getStorageReference())->shouldBeCalled();

        $this->sut->removeFileFromS3($reportPdf);
    }

    /** @test */
    public function removeFileFromS3_missing_storage_ref()
    {
        self::expectException(Exception::class);

        $reportPdf = DocumentHelper::createReportPdfDocument();
        $reportPdf->setStorageReference(null);

        $this->storage->removeFromS3($reportPdf->getStorageReference())->shouldNotBeCalled();

        $this->sut->removeFileFromS3($reportPdf);
    }
}
