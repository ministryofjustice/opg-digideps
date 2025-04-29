<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Model\MissingDocument;
use App\Model\RetrievedDocument;
use App\Service\Client\RestClient;
use App\Service\File\Storage\FileNotFoundException;
use App\Service\File\Storage\S3Storage;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DocumentServiceTest extends TestCase
{
    use ProphecyTrait;

    protected DocumentService $object;
    private ObjectProphecy|S3Storage $s3Storage;
    private ObjectProphecy|RestClient $restClient;
    private ObjectProphecy|Environment $twig;
    private ObjectProphecy|LoggerInterface $logger;
    private ObjectProphecy|Document $doc1;
    private ObjectProphecy|Document $doc2;
    private ObjectProphecy|Document $doc3;
    private ObjectProphecy|Document $doc4;

    public function setUp(): void
    {
        /** @var ObjectProphecy|S3Storage $s3Storage */
        $s3Storage = self::prophesize(S3Storage::class);
        /** @var ObjectProphecy|RestClient $restClient */
        $restClient = self::prophesize(RestClient::class);
        /** @var ObjectProphecy|LoggerInterface $logger */
        $logger = self::prophesize(LoggerInterface::class);
        /** @var ObjectProphecy|Environment $twig */
        $twig = self::prophesize(Environment::class);

        $this->s3Storage = $s3Storage;
        $this->restClient = $restClient;
        $this->logger = $logger;
        $this->twig = $twig;

        $this->object = new DocumentService($this->s3Storage->reveal(), $this->restClient->reveal(), $this->logger->reveal(), $this->twig->reveal());

        $this->doc1 = self::prophesize(Document::class);
        $this->doc1->getStorageReference()->willReturn('ref-1');
        $this->doc1->getId()->willReturn(1);
        $this->doc1->getFileName()->willReturn('file-name1.pdf');

        $this->doc2 = self::prophesize(Document::class);
        $this->doc2->getStorageReference()->willReturn('ref-2');
        $this->doc2->getId()->willReturn(2);
        $this->doc2->getFileName()->willReturn('file-name2.pdf');

        $this->doc3 = self::prophesize(Document::class);
        $this->doc3->getStorageReference()->willReturn('ref-3');
        $this->doc3->getId()->willReturn(3);
        $this->doc3->getFileName()->willReturn('file-name3.pdf');

        $this->doc4 = self::prophesize(Document::class);
        $this->doc4->getStorageReference()->willReturn('ref-4');
        $this->doc4->getId()->willReturn(4);
        $this->doc4->getFileName()->willReturn('file-name4.pdf');
    }

    public function testRemoveDocumentFromS3(): void
    {
        $docId = 1;
        $document = new Document();
        $document->setId($docId);
        $document->setStorageReference('r1');

        $this->s3Storage
            ->removeFromS3('r1')
            ->shouldBeCalled()
            ->willReturn([]);

        $this->restClient
            ->delete('document/'.$docId)
            ->shouldBeCalled()
            ->willReturn(['id' => 1]);

        $this->object->removeDocumentFromS3($document);
    }

    public function testRemoveDocumentWithS3Failure(): void
    {
        $docId = 1;

        $document = new Document();
        $document->setId($docId);
        $document->setStorageReference('r1');

        $this->s3Storage
            ->removeFromS3('r1')
            ->shouldBeCalled()
            ->willThrow(Exception::class);

        $this->restClient
            ->delete(Argument::cetera())
            ->shouldNotBeCalled();

        $this->expectException(Exception::class);

        $this->object->removeDocumentFromS3($document);
    }

    public function testRetrieveDocumentsFromS3ByReportSubmission(): void
    {
        $this->s3Storage->retrieve('ref-1')->shouldBeCalled()->willReturn('doc1 contents');
        $this->s3Storage->retrieve('ref-2')->shouldBeCalled()->willReturn('doc2 contents');

        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc1->reveal(), $this->doc2->reveal()]));

        [$documents, $missing] = $this->object->retrieveDocumentsFromS3ByReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('doc1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name2.pdf');
        $expectedRetrievedDoc2->setContent('doc2 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission->reveal());

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2], $documents);
        self::assertEmpty($missing);
    }

    public function testMissingDocumentsFileNamesAreReturnedIfNotRetrievable(): void
    {
        $this->s3Storage->retrieve('ref-1')->shouldBeCalled()->willReturn('doc1 contents');
        $this->s3Storage->retrieve('ref-2')->shouldBeCalled()
            ->willThrow(new FileNotFoundException('Cannot find file with reference ref-2'));

        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc1->reveal(), $this->doc2->reveal()]));

        [$documents, $missing] = $this->object->retrieveDocumentsFromS3ByReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc = new RetrievedDocument();
        $expectedRetrievedDoc->setFileName('file-name1.pdf');
        $expectedRetrievedDoc->setContent('doc1 contents');
        $expectedRetrievedDoc->setReportSubmission($reportSubmission->reveal());

        $expectedMissingDoc = new MissingDocument();
        $expectedMissingDoc->setFileName('file-name2.pdf');
        $expectedMissingDoc->setReportSubmission($reportSubmission->reveal());

        self::assertEquals([$expectedRetrievedDoc], $documents);
        self::assertEquals([$expectedMissingDoc], $missing);
    }

    public function testRetrieveDocumentsFromS3ByReportSubmissions(): void
    {
        $this->s3Storage->retrieve('ref-1')->shouldBeCalled()->willReturn('doc1 contents');
        $this->s3Storage->retrieve('ref-2')->shouldBeCalled()->willReturn('doc2 contents');
        $this->s3Storage->retrieve('ref-3')->shouldBeCalled()->willReturn('doc3 contents');

        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc1->reveal(), $this->doc2->reveal()]));

        /** @var ObjectProphecy|ReportSubmission $reportSubmission2 */
        $reportSubmission2 = self::prophesize(ReportSubmission::class);
        $reportSubmission2->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc3->reveal()]));

        [$documents, $missing] = $this->object->retrieveDocumentsFromS3ByReportSubmissions(
            [$reportSubmission->reveal(), $reportSubmission2->reveal()]
        );

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('doc1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name2.pdf');
        $expectedRetrievedDoc2->setContent('doc2 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc3 = new RetrievedDocument();
        $expectedRetrievedDoc3->setFileName('file-name3.pdf');
        $expectedRetrievedDoc3->setContent('doc3 contents');
        $expectedRetrievedDoc3->setReportSubmission($reportSubmission2->reveal());

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2, $expectedRetrievedDoc3], $documents);
        self::assertEmpty($missing);
    }

    public function testRetrieveDocumentsFromS3ByReportSubmissionsMissingDocs(): void
    {
        $this->s3Storage->retrieve('ref-1')->shouldBeCalled()->willReturn('doc1 contents');
        $this->s3Storage->retrieve('ref-2')->shouldBeCalled()
            ->willThrow(new FileNotFoundException('Cannot find file with reference ref-2'));
        $this->s3Storage->retrieve('ref-3')->shouldBeCalled()
            ->willThrow(new FileNotFoundException('Cannot find file with reference ref-3'));
        $this->s3Storage->retrieve('ref-4')->shouldBeCalled()->willReturn('doc4 contents');

        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc1->reveal(), $this->doc2->reveal()]));

        /** @var ObjectProphecy|ReportSubmission $reportSubmission2 */
        $reportSubmission2 = self::prophesize(ReportSubmission::class);
        $reportSubmission2->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc3->reveal(), $this->doc4->reveal()]));

        [$documents, $missing] = $this->object->retrieveDocumentsFromS3ByReportSubmissions(
            [$reportSubmission->reveal(), $reportSubmission2->reveal()]
        );

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('doc1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name4.pdf');
        $expectedRetrievedDoc2->setContent('doc4 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission2->reveal());

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2], $documents);

        $expectedMissingDoc1 = new MissingDocument();
        $expectedMissingDoc1->setFileName('file-name2.pdf');
        $expectedMissingDoc1->setReportSubmission($reportSubmission->reveal());

        $expectedMissingDoc2 = new MissingDocument();
        $expectedMissingDoc2->setFileName('file-name3.pdf');
        $expectedMissingDoc2->setReportSubmission($reportSubmission2->reveal());

        self::assertEquals([$expectedMissingDoc1, $expectedMissingDoc2], $missing);
    }

    public function testCreateMissingDocumentsFlashMessage(): void
    {
        $missingDoc = new MissingDocument();
        $missingDocuments = [$missingDoc];

        $expectedFlash = 'some flash message here';

        $this->twig
            ->render('@App/FlashMessages/missing-documents.html.twig', ['missingDocuments' => $missingDocuments])
            ->shouldBeCalled()
            ->willReturn($expectedFlash);

        $actualFlash = $this->object->createMissingDocumentsFlashMessage($missingDocuments);

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

        $loader = new FilesystemLoader([__DIR__.'/../../../templates/FlashMessages']);

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
