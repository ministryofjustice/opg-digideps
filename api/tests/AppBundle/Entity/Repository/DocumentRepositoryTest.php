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
    private $reportSubmission, $additionalReportSubmission, $ndrSubmission, $resubmittedReportSubmission, $resubmittedReportAdditionalSubmission;

    /** @var DocumentRepository */
    private $documentRepository;

    /** @var User */
    private $user;

    /** @var Document */
    private $reportPdfDocument,
        $supportingDocument,
        $supportingDocumentAfterSubmission,
        $ndrReportPdfDocument,
        $resubmitReportPdfDocument,
        $supportingDocumentWithResubmission,
        $supportingDocumentAfterResubmission;

    /** @var DateTime */
    private $firstJuly, $secondJulyAm, $secondJulyPm, $thirdJulyAm, $thirdJulyPm, $fourthJulyAm, $fourthJulyPm, $fifthJulyAm, $fifthJulyPm;

    /** @var Ndr */
    private $ndr;

    /** @var string */
    private $uniq;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->firstJuly = DateTime::createFromFormat('d/m/Y', '01/07/2020', new DateTimeZone('UTC'));
        $this->secondJulyAm = DateTime::createFromFormat('d/m/Y', '02/07/2020', new DateTimeZone('UTC'));
        $this->secondJulyPm = clone $this->secondJulyAm->add(new DateInterval('PT20H'));
        $this->thirdJulyAm = DateTime::createFromFormat('d/m/Y', '03/07/2020', new DateTimeZone('UTC'));
        $this->thirdJulyPm = clone $this->thirdJulyAm->add(new DateInterval('PT20H'));
        $this->fourthJulyAm = DateTime::createFromFormat('d/m/Y', '04/07/2020', new DateTimeZone('UTC'));
        $this->fourthJulyPm = clone $this->fourthJulyAm->add(new DateInterval('PT20H'));
        $this->fifthJulyAm = DateTime::createFromFormat('d/m/Y', '05/07/2020', new DateTimeZone('UTC'));
        $this->fifthJulyPm = clone $this->fifthJulyAm->add(new DateInterval('PT20H'));

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
            $this->firstJuly,
            $this->firstJuly->add(new DateInterval('P364D')))
        )->setSubmitDate($this->secondJulyPm);

        // Initial report PDF submission with supporting docs
        $this->reportSubmission = (new ReportSubmission($this->report, $this->user))
            ->setUuid('abc-123-abc-123')
            ->setCreatedOn($this->secondJulyPm);

        $this->reportPdfDocument = (new Document($this->report))
            ->setReportSubmission($this->reportSubmission)
            ->setFileName('report.pdf')
            ->setStorageReference('storage-ref-report.pdf')
            ->setIsReportPdf(true)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED)
            ->setCreatedOn($this->secondJulyAm);

        $this->supportingDocument = (new Document($this->report))
            ->setReportSubmission($this->reportSubmission)
            ->setFileName('supporting-document.pdf')
            ->setStorageReference('storage-ref-supporting.pdf')
            ->setIsReportPdf(false)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED)
            ->setCreatedOn($this->secondJulyAm);

        $this->reportSubmission
            ->addDocument($this->reportPdfDocument)
            ->addDocument($this->supportingDocument);

        // Additional docs for submission
        $this->additionalReportSubmission = (new ReportSubmission($this->report, $this->user))
            ->setUuid(null)
            ->setCreatedOn($this->thirdJulyPm);

        $this->supportingDocumentAfterSubmission = (new Document($this->report))
            ->setReportSubmission($this->additionalReportSubmission)
            ->setFileName('supporting-document-additional.pdf')
            ->setStorageReference('storage-ref-supporting-additional.pdf')
            ->setIsReportPdf(false)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED)
            ->setCreatedOn($this->thirdJulyAm);

        $this->additionalReportSubmission
            ->addDocument($this->supportingDocumentAfterSubmission);

        // Resubmission
        $this->resubmittedReportSubmission = (new ReportSubmission($this->report, $this->user))
            ->setUuid('def-456-def-456')
            ->setCreatedOn($this->fourthJulyPm);

        $this->resubmitReportPdfDocument = (new Document($this->report))
            ->setReportSubmission($this->resubmittedReportSubmission)
            ->setFileName('resubmit-report.pdf')
            ->setStorageReference('storage-ref-resubmit-report.pdf')
            ->setIsReportPdf(true)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED)
            ->setCreatedOn($this->fourthJulyAm);

        $this->supportingDocumentWithResubmission = (new Document($this->report))
            ->setReportSubmission($this->resubmittedReportSubmission)
            ->setFileName('supporting-document-resubmit.pdf')
            ->setStorageReference('storage-ref-resubmit-supporting.pdf')
            ->setIsReportPdf(false)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED)
            ->setCreatedOn($this->fourthJulyAm);

        $this->resubmittedReportSubmission
            ->addDocument($this->resubmitReportPdfDocument)
            ->addDocument($this->supportingDocumentWithResubmission);

        // Additional docs for resubmission
        $this->resubmittedReportAdditionalSubmission = (new ReportSubmission($this->report, $this->user))
            ->setUuid(null)
            ->setCreatedOn($this->fifthJulyPm);

        $this->supportingDocumentAfterResubmission = (new Document($this->report))
            ->setReportSubmission($this->resubmittedReportAdditionalSubmission)
            ->setFileName('supporting-document-additional-after-resub.pdf')
            ->setStorageReference('storage-ref-supporting-additional-after-resub.pdf')
            ->setIsReportPdf(false)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED)
            ->setCreatedOn($this->fifthJulyAm);

        $this->resubmittedReportAdditionalSubmission
            ->addDocument($this->supportingDocumentAfterResubmission);

        // setup NDR documents
        $this->ndr = (new Ndr($this->client))
            ->setStartDate($this->firstJuly)
            ->setSubmitDate($this->secondJulyPm);

        $this->ndrSubmission = (new ReportSubmission($this->ndr, $this->user))
            ->setUuid('cba-123-cba-123');

        $this->ndrReportPdfDocument = (new Document($this->ndr))
            ->setReportSubmission($this->ndrSubmission)
            ->setFileName('ndr-report.pdf')
            ->setStorageReference('storage-ref-ndr-report.pdf')
            ->setIsReportPdf(true)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED)
            ->setCreatedOn($this->secondJulyAm);;

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

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress('100');

        $reportPdf = $this->documentRepository->find($this->reportPdfDocument->getId());
        $supportingDocument = $this->documentRepository->find($this->supportingDocument->getId());

        $this->entityManager->refresh($reportPdf);
        $this->entityManager->refresh($supportingDocument);

        $this->assertDataMatchesEntity($documents, $reportPdf, $this->client, $this->reportSubmission, $this->report);
        $this->assertDataMatchesEntity($documents, $supportingDocument, $this->client, $this->reportSubmission, $this->report);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $reportPdf->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $supportingDocument->getSynchronisationStatus());
    }

    /**
     * @test
     */
    public function getQueuedDocumentsAndSetToInProgress_supportsNdrs()
    {
        $this->persistEntities();

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress('100');

        $this->assertDataMatchesEntity($documents, $this->ndrReportPdfDocument, $this->client, $this->ndrSubmission, $this->ndr);

        $ndrReportPdfDocument = $this->documentRepository->find($this->ndrReportPdfDocument->getId());
        $this->entityManager->refresh($ndrReportPdfDocument);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $ndrReportPdfDocument->getSynchronisationStatus());
    }

    /**
     * @test
     */
    public function additionalDocumentsSubmissionsUseOriginalSubmissionUUID()
    {
        $this->persistEntities();

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');
        self::assertEquals($this->reportSubmission->getUuid(), $documents[$this->supportingDocumentAfterSubmission->getId()]['report_submission_uuid']);
    }

    /**
     * @test
     */
    public function ResubmissionsUseTheirOwnSubmissionUUID()
    {
        $this->persistEntities();

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        $resubmitReportPdf = $this->documentRepository->find($this->resubmitReportPdfDocument->getId());
        $supportingDocumentWithResubmission = $this->documentRepository->find($this->supportingDocumentWithResubmission->getId());

        $this->entityManager->refresh($resubmitReportPdf);
        $this->entityManager->refresh($supportingDocumentWithResubmission);

        self::assertEquals($this->resubmittedReportSubmission->getUuid(), $documents[$resubmitReportPdf->getId()]['report_submission_uuid']);
        self::assertEquals($this->resubmittedReportSubmission->getUuid(), $documents[$supportingDocumentWithResubmission->getId()]['report_submission_uuid']);
    }

    /**
     * @test
     */
    public function additionalDocsOnResubmissionsUseResubmissionUUID()
    {
        $this->persistEntities();

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        self::assertEquals($this->resubmittedReportSubmission->getUuid(), $documents[$this->supportingDocumentAfterResubmission->getId()]['report_submission_uuid']);
    }

    /** @test */
    public function updateSupportingDocumentStatusByReportSubmissionIds()
    {
        $this->reportPdfDocument->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
        $this->persistEntities();

        $updatedDocumentsCount = $this->documentRepository
            ->updateSupportingDocumentStatusByReportSubmissionIds(
                [$this->reportSubmission->getId(), $this->additionalReportSubmission->getId()],
                'An error message'
            );

        $this->refreshDocumentEntities();

        $this->assertEquals(3, $updatedDocumentsCount);

        foreach([$this->supportingDocument, $this->supportingDocumentAfterSubmission] as $doc) {
            self::assertEquals(Document::SYNC_STATUS_PERMANENT_ERROR, $doc->getSynchronisationStatus());
            self::assertEquals('An error message', $doc->getSynchronisationError());
        }

        self::assertEquals(Document::SYNC_STATUS_PERMANENT_ERROR, $this->reportPdfDocument->getSynchronisationStatus());
        self::assertEquals(null, $this->reportPdfDocument->getSynchronisationError());
    }

    private function persistEntities()
    {
        $this->user->setEmail(sprintf('test-user%s%s@test.com', $this->uniq, rand(0, 100000)));

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

        $this->entityManager->persist($this->supportingDocumentWithResubmission);
        $this->entityManager->persist($this->resubmitReportPdfDocument);
        $this->entityManager->persist($this->resubmittedReportSubmission);

        $this->entityManager->persist($this->supportingDocumentAfterResubmission);
        $this->entityManager->persist($this->resubmittedReportAdditionalSubmission);

        $this->entityManager->flush();
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
        self::assertEquals($submission->getUuid(), $documents[$docId]['report_submission_uuid']);

        if ($report instanceof Report) {
            self::assertEquals($report->getEndDate()->format('Y-m-d'), $documents[$docId]['report_end_date']);
            self::assertEquals($report->getType(), $documents[$docId]['report_type']);
        }
    }

    private function refreshDocumentEntities()
    {
        $this->entityManager->refresh($this->reportPdfDocument);
        $this->entityManager->refresh($this->supportingDocument);
        $this->entityManager->refresh($this->supportingDocumentAfterSubmission);
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
