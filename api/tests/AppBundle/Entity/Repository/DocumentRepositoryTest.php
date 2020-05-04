<?php declare(strict_types=1);

namespace Tests\AppBundle\Entity\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\Repository\DocumentRepository;
use AppBundle\Entity\User;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DocumentRepositoryTest extends KernelTestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var Client */
    private $client;

    /** @var Report */
    private $report;

    /** @var ReportSubmission */
    private $reportSubmission, $additionalReportSubmission, $ndrSubmission;

    /** @var DocumentRepository */
    private $documentRepository;

    /** @var User */
    private $user;

    /** @var Document */
    private $reportPdfDocument, $supportingDocument, $supportingDocumentAfterSubmission, $ndrReportPdfDocument;

    /** @var DateTime */
    private $now;

    /** @var Ndr */
    private $ndr;

    /** @var string */
    private $uniq;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->now = new DateTime('now', new DateTimeZone('UTC'));

        // Set up Report documents
        $this->uniq = (string) (new DateTime())->getTimestamp();
        $this->user = (new User())
            ->setFirstname('Test')
            ->setLastname('User')
            ->setPassword('password123');

        $this->client = (new Client())
            ->setCaseNumber('acb123');

        $this->report = (new Report(
            $this->client,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            $this->now,
            $this->now->add(new DateInterval('P1D')))
        )->setSubmitDate($this->now->add(new DateInterval('P2D')));

        $this->reportSubmission = (new ReportSubmission($this->report, $this->user))
            ->setUuid('abc-123-abc-123');

        $this->additionalReportSubmission = (new ReportSubmission($this->report, $this->user))
            ->setUuid('def-456-def-456');

        $this->reportPdfDocument = (new Document($this->report))
            ->setReportSubmission($this->reportSubmission)
            ->setFileName('report.pdf')
            ->setStorageReference('storage-ref-report.pdf')
            ->setIsReportPdf(true)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);

        $this->supportingDocument = (new Document($this->report))
            ->setReportSubmission($this->reportSubmission)
            ->setFileName('supporting-document.pdf')
            ->setStorageReference('storage-ref-supporting.pdf')
            ->setIsReportPdf(false)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);

        $this->reportSubmission
            ->addDocument($this->reportPdfDocument)
            ->addDocument($this->supportingDocument);

        $this->supportingDocumentAfterSubmission = (new Document($this->report))
            ->setReportSubmission($this->additionalReportSubmission)
            ->setFileName('supporting-document-additional.pdf')
            ->setStorageReference('storage-ref-supporting-additional.pdf')
            ->setIsReportPdf(false)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);

        $this->additionalReportSubmission
            ->addDocument($this->supportingDocumentAfterSubmission);

        // setup NDR documents
        $this->ndr = (new Ndr($this->client))
            ->setStartDate($this->now)
            ->setSubmitDate($this->now->add(new DateInterval('P2D')));

        $this->ndrSubmission = (new ReportSubmission($this->ndr, $this->user))
            ->setUuid('cba-123-cba-123');

        $this->ndrReportPdfDocument = (new Document($this->ndr))
            ->setReportSubmission($this->ndrSubmission)
            ->setFileName('ndr-report.pdf')
            ->setStorageReference('storage-ref-ndr-report.pdf')
            ->setIsReportPdf(true)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);

        $this->ndrSubmission
            ->addDocument($this->ndrReportPdfDocument);

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->documentRepository = $this->entityManager
            ->getRepository(Document::class);
    }

    /**
     * @test
     */
    public function getQueuedDocumentsAndSetToInProgress()
    {
        $this->persistEntities();
        $this->entityManager->flush();

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress();

        $reportPdf = $this->documentRepository->find($this->reportPdfDocument->getId());
        $supportingDocument = $this->documentRepository->find($this->supportingDocument->getId());

        $this->entityManager->refresh($reportPdf);
        $this->entityManager->refresh($supportingDocument);

        $this->assertDataMatchesEntity($documents, $this->reportPdfDocument, $this->client, $this->reportSubmission, $this->report);
        $this->assertDataMatchesEntity($documents, $this->supportingDocument, $this->client, $this->reportSubmission, $this->report);
        $this->assertDataMatchesEntity($documents, $this->supportingDocumentAfterSubmission, $this->client, $this->additionalReportSubmission, $this->report);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $reportPdf->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $supportingDocument->getSynchronisationStatus());
    }

    /**
     * @test
     */
    public function multipleReportSubmissionsAreReturned()
    {
        $this->persistEntities();
        $this->entityManager->flush();

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress();

        $this->assertEquals(2, count($documents[$this->reportPdfDocument->getId()]['report_submissions']));
    }

    /**
     * @test
     */
    public function supportsNdrs()
    {
        $this->persistEntities();
        $this->entityManager->flush();

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress();

        $this->assertDataMatchesEntity($documents, $this->ndrReportPdfDocument, $this->client, $this->ndrSubmission, $this->ndr);
        $this->assertEquals(1, count($documents[$this->ndrReportPdfDocument->getId()]['report_submissions']));
    }

    private function persistEntities()
    {
        $this->user->setEmail(sprintf('test-user%s%s@test.com', $this->uniq, rand(0, 10000)));

        $this->entityManager->persist($this->user);
        $this->entityManager->persist($this->client);
        $this->entityManager->persist($this->report);
        $this->entityManager->persist($this->reportPdfDocument);
        $this->entityManager->persist($this->supportingDocument);
        $this->entityManager->persist($this->reportSubmission);
        $this->entityManager->persist($this->supportingDocumentAfterSubmission);
        $this->entityManager->persist($this->additionalReportSubmission);
        $this->entityManager->persist($this->ndr);
        $this->entityManager->persist($this->ndrReportPdfDocument);
        $this->entityManager->persist($this->ndrSubmission);
    }

    /**
     * @param array $documents
     * @param Document $document
     * @param Client $client
     * @param ReportSubmission $submission
     * @param Report|Ndr $report
     */
    private function assertDataMatchesEntity(
        array $documents,
        Document $document,
        Client $client,
        ReportSubmission $submission,
        $report
    )
    {
        $docId = $document->getId();

        self::assertEquals($document->getId(), $documents[$docId]['document_id']);
        self::assertEquals($submission->getId(), $documents[$docId]['report_submission_id']);
        self::assertEquals($client->getCaseNumber(), $documents[$docId]['case_number']);
        self::assertEquals($document->isReportPdf(), $documents[$docId]['is_report_pdf']);
        self::assertEquals($document->getStorageReference(), $documents[$docId]['storage_reference']);
        self::assertEquals($report->getStartDate()->format('Y-m-d'), $documents[$docId]['report_start_date']);
        self::assertEquals($report->getSubmitDate()->format('Y-m-d H:i:s'), $documents[$docId]['report_submit_date']);

        if ($report instanceof Report) {
            self::assertEquals($report->getEndDate()->format('Y-m-d'), $documents[$docId]['report_end_date']);
            self::assertEquals($report->getType(), $documents[$docId]['report_type']);
        }

    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
