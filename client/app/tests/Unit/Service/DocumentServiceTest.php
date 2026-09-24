<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service;

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Report\ReportSubmission;
use OPG\Digideps\Frontend\Model\MissingDocument;
use OPG\Digideps\Frontend\Model\RetrievedDocument;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\DocumentService;
use OPG\Digideps\Frontend\Service\File\Storage\FileNotFoundException;
use OPG\Digideps\Frontend\Service\File\Storage\S3Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DocumentServiceTest extends TestCase
{
    private S3Storage&MockObject $s3Storage;
    private RestClient&MockObject $restClient;
    private Environment&MockObject $twig;
    private Document&MockObject $doc1;
    private Document&MockObject $doc2;
    private Document&MockObject $doc3;
    private Document&MockObject $doc4;

    protected DocumentService $sut;

    public function setUp(): void
    {
        $this->s3Storage = self::createMock(S3Storage::class);
        $this->restClient = self::createMock(RestClient::class);
        $this->twig = self::createMock(Environment::class);

        $this->sut = new DocumentService(
            $this->s3Storage,
            $this->restClient,
            self::createMock(LoggerInterface::class),
            $this->twig
        );

        $this->doc1 = self::createMock(Document::class);
        $this->doc1->method('getStorageReference')->willReturn('ref-1');
        $this->doc1->method('getId')->willReturn(1);
        $this->doc1->method('getFileName')->willReturn('file-name1.pdf');

        $this->doc2 = self::createMock(Document::class);
        $this->doc2->method('getStorageReference')->willReturn('ref-2');
        $this->doc2->method('getId')->willReturn(2);
        $this->doc2->method('getFileName')->willReturn('file-name2.pdf');

        $this->doc3 = self::createMock(Document::class);
        $this->doc3->method('getStorageReference')->willReturn('ref-3');
        $this->doc3->method('getId')->willReturn(3);
        $this->doc3->method('getFileName')->willReturn('file-name3.pdf');

        $this->doc4 = self::createMock(Document::class);
        $this->doc4->method('getStorageReference')->willReturn('ref-4');
        $this->doc4->method('getId')->willReturn(4);
        $this->doc4->method('getFileName')->willReturn('file-name4.pdf');
    }

    public function testRemoveDocumentFromS3(): void
    {
        $docId = 1;
        $document = new Document();
        $document->setId($docId);
        $document->setStorageReference('r1');

        $this->s3Storage->expects(self::once())
            ->method('removeFromS3')
            ->with('r1')
            ->willReturn([]);

        $this->restClient->expects(self::once())
            ->method('delete')
            ->with('document/' . $docId)
            ->willReturn(['id' => 1]);

        $this->sut->removeDocumentFromS3($document);
    }

    public function testRemoveDocumentWithS3Failure(): void
    {
        $document = new Document();
        $document->setId(1);
        $document->setStorageReference('r1');

        $this->s3Storage->expects(self::once())
            ->method('removeFromS3')
            ->willThrowException(new \Exception());

        $this->restClient->expects(self::never())->method('delete');

        self::expectException(\Exception::class);

        $this->sut->removeDocumentFromS3($document);
    }

    public function testRetrieveDocumentsFromS3ByReportSubmission(): void
    {
        $this->s3Storage->expects(self::exactly(2))
            ->method('retrieve')
            ->willReturnCallback(fn (string $ref) => "{$ref} contents");

        $reportSubmission = self::createMock(ReportSubmission::class);
        $reportSubmission->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->doc1, $this->doc2]);

        [$documents, $missing] = $this->sut->retrieveDocumentsFromS3ByReportSubmission($reportSubmission);

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('ref-1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission);

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name2.pdf');
        $expectedRetrievedDoc2->setContent('ref-2 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission);

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2], $documents);
        self::assertEmpty($missing);
    }

    public function testMissingDocumentsFileNamesAreReturnedIfNotRetrievable(): void
    {
        $this->s3Storage->expects(self::exactly(2))
            ->method('retrieve')
            ->willReturnCallback(function (string $ref) {
                return match ($ref) {
                    'ref-1' => "{$ref} contents",
                    'ref-2' => throw new FileNotFoundException('Cannot find file with reference ref-2'),
                    default => throw new \Exception(),
                };
            });

        $reportSubmission = self::createMock(ReportSubmission::class);
        $reportSubmission->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->doc1, $this->doc2]);

        [$documents, $missing] = $this->sut->retrieveDocumentsFromS3ByReportSubmission($reportSubmission);

        $expectedRetrievedDoc = new RetrievedDocument();
        $expectedRetrievedDoc->setFileName('file-name1.pdf');
        $expectedRetrievedDoc->setContent('ref-1 contents');
        $expectedRetrievedDoc->setReportSubmission($reportSubmission);

        $expectedMissingDoc = new MissingDocument();
        $expectedMissingDoc->setFileName('file-name2.pdf');
        $expectedMissingDoc->setReportSubmission($reportSubmission);

        self::assertEquals([$expectedRetrievedDoc], $documents);
        self::assertEquals([$expectedMissingDoc], $missing);
    }

    public function testRetrieveDocumentsFromS3ByReportSubmissions(): void
    {
        $this->s3Storage->expects(self::exactly(3))
            ->method('retrieve')
            ->willReturnCallback(fn (string $ref) => "{$ref} contents");

        $reportSubmission = self::createMock(ReportSubmission::class);
        $reportSubmission->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->doc1, $this->doc2]);

        $reportSubmission2 = self::createMock(ReportSubmission::class);
        $reportSubmission2->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->doc3]);

        [$documents, $missing] = $this->sut->retrieveDocumentsFromS3ByReportSubmissions(
            [$reportSubmission, $reportSubmission2]
        );

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('ref-1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission);

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name2.pdf');
        $expectedRetrievedDoc2->setContent('ref-2 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission);

        $expectedRetrievedDoc3 = new RetrievedDocument();
        $expectedRetrievedDoc3->setFileName('file-name3.pdf');
        $expectedRetrievedDoc3->setContent('ref-3 contents');
        $expectedRetrievedDoc3->setReportSubmission($reportSubmission2);

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2, $expectedRetrievedDoc3], $documents);
        self::assertEmpty($missing);
    }

    public function testRetrieveDocumentsFromS3ByReportSubmissionsMissingDocs(): void
    {
        $this->s3Storage->expects(self::exactly(4))
            ->method('retrieve')
            ->willReturnCallback(function (string $ref) {
                return match ($ref) {
                    'ref-1', 'ref-4' => "{$ref} contents",
                    'ref-2', 'ref-3' => throw new FileNotFoundException("Cannot find file with reference {$ref}"),
                    default => throw new \Exception(),
                };
            });

        $reportSubmission = self::createMock(ReportSubmission::class);
        $reportSubmission->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->doc1, $this->doc2]);

        $reportSubmission2 = self::createMock(ReportSubmission::class);
        $reportSubmission2->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->doc3, $this->doc4]);

        [$documents, $missing] = $this->sut->retrieveDocumentsFromS3ByReportSubmissions(
            [$reportSubmission, $reportSubmission2]
        );

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('ref-1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission);

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name4.pdf');
        $expectedRetrievedDoc2->setContent('ref-4 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission2);

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2], $documents);

        $expectedMissingDoc1 = new MissingDocument();
        $expectedMissingDoc1->setFileName('file-name2.pdf');
        $expectedMissingDoc1->setReportSubmission($reportSubmission);

        $expectedMissingDoc2 = new MissingDocument();
        $expectedMissingDoc2->setFileName('file-name3.pdf');
        $expectedMissingDoc2->setReportSubmission($reportSubmission2);

        self::assertEquals([$expectedMissingDoc1, $expectedMissingDoc2], $missing);
    }

    public function testCreateMissingDocumentsFlashMessage(): void
    {
        $missingDoc = new MissingDocument();
        $missingDocuments = [$missingDoc];

        $expectedFlash = 'some flash message here';

        $this->twig->expects(self::once())
            ->method('render')
            ->with('@App/FlashMessages/missing-documents.html.twig', ['missingDocuments' => $missingDocuments])
            ->willReturn($expectedFlash);

        $actualFlash = $this->sut->createMissingDocumentsFlashMessage($missingDocuments);

        self::assertEquals($expectedFlash, $actualFlash);
    }

    public function testTwigTemplate(): void
    {
        $reportSubmission1 = $this->generateReportSubmission('CaseNumber1');
        $reportSubmission2 = $this->generateReportSubmission('CaseNumber2');

        $missingDoc1 = new MissingDocument();
        $missingDoc1->setFileName('file-name1.pdf');
        $missingDoc1->setReportSubmission($reportSubmission1);

        $missingDoc2 = new MissingDocument();
        $missingDoc2->setFileName('file-name2.pdf');
        $missingDoc2->setReportSubmission($reportSubmission2);

        $missingDoc3 = new MissingDocument();
        $missingDoc3->setFileName('file-name3.pdf');
        $missingDoc3->setReportSubmission($reportSubmission1);

        $missingDocuments = [$missingDoc1, $missingDoc2, $missingDoc3];
        $missingDocumentCaseNumbers = ['CaseNumber1', 'CaseNumber2', 'CaseNumber1'];

        $loader = new FilesystemLoader([__DIR__ . '/../../../templates/FlashMessages']);

        $sut = new Environment($loader);

        $renderedTwig = $sut->render('missing-documents.html.twig', ['missingDocuments' => $missingDocuments]);

        self::assertStringContainsString('The following documents could not be downloaded:', $renderedTwig);

        foreach ($missingDocuments as $index => $missingDocument) {
            $caseNumber = $missingDocumentCaseNumbers[$index];
            $fileName = $missingDocument->getFileName();

            $expectedListItem = "<li>{$caseNumber} - {$fileName}</li>";
            self::assertStringContainsString($expectedListItem, $renderedTwig);
        }
    }

    private function generateReportSubmission(string $caseNumber): ReportSubmission
    {
        $client = new Client();
        $client->setCaseNumber($caseNumber);

        $report = new Report();
        $report->setClient($client);

        $reportSubmission = new ReportSubmission();
        $reportSubmission->setReport($report);

        return $reportSubmission;
    }
}
