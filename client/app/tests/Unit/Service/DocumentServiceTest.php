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
    private MockObject&S3Storage $s3Storage;
    private MockObject&RestClient $restClient;
    private MockObject&Environment $twig;
    private MockObject&LoggerInterface $logger;
    private DocumentService $sut;

    public function setUp(): void
    {
        $this->s3Storage = self::createMock(S3Storage::class);
        $this->restClient = self::createMock(RestClient::class);
        $this->logger = self::createMock(LoggerInterface::class);
        $this->twig = self::createMock(Environment::class);

        $this->sut = new DocumentService($this->s3Storage, $this->restClient, $this->logger, $this->twig);
    }

    public function testRemoveDocumentFromS3(): void
    {
        $document = new Document();
        $document->setId(1);
        $document->setStorageReference('r1');

        $this->s3Storage->expects(self::once())
            ->method('removeFromS3')
            ->with('r1')
            ->willReturn([]);

        $this->restClient->expects(self::once())
            ->method('delete')
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
            ->with('r1')
            ->willThrowException(new \Exception());

        $this->restClient->expects(self::never())
            ->method('delete');

        $this->expectException(\Exception::class);

        $this->sut->removeDocumentFromS3($document);
    }

    public function testRetrieveDocumentsFromS3ByReportSubmission(): void
    {
        $this->s3Storage->expects(self::exactly(2))
            ->method('retrieve')
            ->willReturnCallback(function (string $docRef) {
                return match ($docRef) {
                    'ref-1' => 'doc1 contents',
                    'ref-2' => 'doc2 contents',
                    default => throw new \InvalidArgumentException('invalid document ref')
                };
            });

        /** @var MockObject&ReportSubmission $reportSubmission */
        $reportSubmission = self::createMock(ReportSubmission::class);
        $reportSubmission->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->getDoc(1), $this->getDoc(2)]);

        ['retrieved' => $documents, 'missing' => $missing] =
            $this->sut->retrieveDocumentsFromS3ByReportSubmission($reportSubmission);

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('doc1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission);

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name2.pdf');
        $expectedRetrievedDoc2->setContent('doc2 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission);

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2], $documents);
        self::assertEmpty($missing);
    }

    public function testMissingDocumentsFileNamesAreReturnedIfNotRetrievable(): void
    {
        $this->s3Storage->expects(self::exactly(2))
            ->method('retrieve')
            ->willReturnCallback(function (string $docRef) {
                return match ($docRef) {
                    'ref-1' => 'doc1 contents',
                    'ref-2' => throw new FileNotFoundException('Cannot find file with reference ref-2'),
                    default => throw new \InvalidArgumentException('invalid document ref')
                };
            });

        /** @var MockObject&ReportSubmission $reportSubmission */
        $reportSubmission = self::createMock(ReportSubmission::class);
        $reportSubmission->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->getDoc(1), $this->getDoc(2)]);

        ['retrieved' => $documents, 'missing' => $missing] =
            $this->sut->retrieveDocumentsFromS3ByReportSubmission($reportSubmission);

        $expectedRetrievedDoc = new RetrievedDocument();
        $expectedRetrievedDoc->setFileName('file-name1.pdf');
        $expectedRetrievedDoc->setContent('doc1 contents');
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
            ->willReturnCallback(function (string $docRef) {
                return match ($docRef) {
                    'ref-1' => 'doc1 contents',
                    'ref-2' => 'doc2 contents',
                    'ref-3' => 'doc3 contents',
                    default => throw new \InvalidArgumentException('invalid document ref')
                };
            });

        /** @var MockObject&ReportSubmission $reportSubmission */
        $reportSubmission = self::createMock(ReportSubmission::class);
        $reportSubmission->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->getDoc(1), $this->getDoc(2)]);

        /** @var MockObject&ReportSubmission $reportSubmission2 */
        $reportSubmission2 = self::createMock(ReportSubmission::class);
        $reportSubmission2->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->getDoc(3)]);

        ['retrieved' => $documents, 'missing' => $missing] =
            $this->sut->retrieveDocumentsFromS3ByReportSubmissions(
                [$reportSubmission, $reportSubmission2]
            );

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('doc1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission);

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name2.pdf');
        $expectedRetrievedDoc2->setContent('doc2 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission);

        $expectedRetrievedDoc3 = new RetrievedDocument();
        $expectedRetrievedDoc3->setFileName('file-name3.pdf');
        $expectedRetrievedDoc3->setContent('doc3 contents');
        $expectedRetrievedDoc3->setReportSubmission($reportSubmission2);

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2, $expectedRetrievedDoc3], $documents);
        self::assertEmpty($missing);
    }

    public function testRetrieveDocumentsFromS3ByReportSubmissionsMissingDocs(): void
    {
        $this->s3Storage->expects(self::exactly(4))
            ->method('retrieve')
            ->willReturnCallback(function (string $docRef) {
                return match ($docRef) {
                    'ref-1' => 'doc1 contents',
                    'ref-2' => throw new FileNotFoundException('Cannot find file with reference ref-2'),
                    'ref-3' => throw new FileNotFoundException('Cannot find file with reference ref-3'),
                    'ref-4' => 'doc4 contents',
                    default => throw new \InvalidArgumentException('invalid document ref')
                };
            });

        /** @var MockObject&ReportSubmission $reportSubmission */
        $reportSubmission = self::createMock(ReportSubmission::class);
        $reportSubmission->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->getDoc(1), $this->getDoc(2)]);

        /** @var MockObject&ReportSubmission $reportSubmission2 */
        $reportSubmission2 = self::createMock(ReportSubmission::class);
        $reportSubmission2->expects(self::once())
            ->method('getDocuments')
            ->willReturn([$this->getDoc(3), $this->getDoc(4)]);

        ['retrieved' => $documents, 'missing' => $missing] =
            $this->sut->retrieveDocumentsFromS3ByReportSubmissions([$reportSubmission, $reportSubmission2]);

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('doc1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission);

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name4.pdf');
        $expectedRetrievedDoc2->setContent('doc4 contents');
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
            ->with(
                '@App/FlashMessages/missing-documents.html.twig',
                ['missingDocuments' => $missingDocuments]
            )
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

    private function getDoc(int $id): Document&MockObject
    {
        $doc = self::createMock(Document::class);
        $doc->method('getStorageReference')->willReturn("ref-{$id}");
        $doc->method('getId')->willReturn($id);
        $doc->method('getFileName')->willReturn("file-name{$id}.pdf");
        return $doc;
    }
}
