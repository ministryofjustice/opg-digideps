<?php declare(strict_types=1);

namespace Tests\AppBundle\Entity\Repository;

use App\Tests\ApiWebTestCase;
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
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DocumentRepositoryTest extends KernelTestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var Client */
    private $client;

    /** @var Report */
    private $firstReport, $secondReport;

    /** @var ReportSubmission */
    private $firstReportSubmission,
        $additionalReportSubmission,
        $ndrSubmission,
        $resubmittedReportSubmission,
        $additionalSupportingDocumentAfterSubmission,
        $secondReportSubmission;

    /** @var DocumentRepository */
    private $documentRepository;

    /** @var User */
    private $user;

    /** @var Document */
    private $firstReportPdfDocument,
        $firstSupportingDocument,
        $supportingDocumentAfterSubmission,
        $ndrReportPdfDocument,
        $resubmittedReportPdfDocument,
        $resubmittedSupportingDocumentWithResubmission,
        $supportingDocumentAfterResubmission,
        $secondReportPdfDocument,
        $secondSupportingDocument;

    /** @var DateTime */
    private $firstJuly, $secondJulyAm, $secondJulyPm, $thirdJulyAm, $thirdJulyPm;

    /** @var Ndr */
    private $ndr;

    /** @var string */
    private $uniq;

    /** @var array */
    private $entities;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->documentRepository = $this->entityManager
            ->getRepository(Document::class);

        $this->purgeDatabase();

        $this->firstJuly = DateTime::createFromFormat('d/m/Y', '01/07/2020', new DateTimeZone('UTC'));
        $this->secondJulyAm = DateTime::createFromFormat('d/m/Y', '02/07/2020', new DateTimeZone('UTC'));
        $this->secondJulyPm = clone $this->secondJulyAm->add(new DateInterval('PT20H'));
        $this->thirdJulyAm = DateTime::createFromFormat('d/m/Y', '03/07/2020', new DateTimeZone('UTC'));
        $this->thirdJulyPm = clone $this->thirdJulyAm->add(new DateInterval('PT20H'));

        // Set up Report documents
        $this->uniq = (string) (new DateTime())->getTimestamp();
        $this->user = (new User())
            ->setFirstname('Test')
            ->setLastname('User')
            ->setPassword('password123');

        $this->client = (new Client())
            ->setCaseNumber('acb123');

        // First Report
        $this->firstReport = (new Report(
            $this->client,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            $this->firstJuly,
            $this->firstJuly->add(new DateInterval('P364D')))
        )->setSubmitDate($this->secondJulyAm);

        // Initial report PDF submission with supporting docs
        $this->firstReportSubmission = (new ReportSubmission($this->firstReport, $this->user))
            ->setCreatedOn($this->secondJulyPm);

        $this->firstReportPdfDocument = (new Document($this->firstReport))
            ->setFileName('report.pdf')
            ->setStorageReference('storage-ref-report.pdf')
            ->setIsReportPdf(true)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED)
            ->setCreatedOn($this->secondJulyAm);

        $this->firstSupportingDocument = (new Document($this->firstReport))
            ->setFileName('supporting-document.pdf')
            ->setStorageReference('storage-ref-supporting.pdf')
            ->setIsReportPdf(false)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);

        // Additional docs for submission
        $this->additionalReportSubmission = (new ReportSubmission($this->firstReport, $this->user))
            ->setUuid(null);

        $this->additionalSupportingDocumentAfterSubmission = (new Document($this->firstReport))
            ->setFileName('supporting-document-additional.pdf')
            ->setStorageReference('storage-ref-supporting-additional.pdf')
            ->setIsReportPdf(false)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
//
//        // Resubmission
//        $this->resubmittedReportSubmission = (new ReportSubmission($this->firstReport, $this->user));
//
//        $this->resubmittedReportPdfDocument = (new Document($this->firstReport))
//            ->setReportSubmission($this->resubmittedReportSubmission)
//            ->setFileName('resubmit-report.pdf')
//            ->setStorageReference('storage-ref-resubmit-report.pdf')
//            ->setIsReportPdf(true)
//            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
//
//        $this->resubmittedSupportingDocumentWithResubmission = (new Document($this->firstReport))
//            ->setReportSubmission($this->resubmittedReportSubmission)
//            ->setFileName('supporting-document-resubmit.pdf')
//            ->setStorageReference('storage-ref-resubmit-supporting.pdf')
//            ->setIsReportPdf(false)
//            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
//
//        $this->resubmittedReportSubmission
//            ->addDocument($this->resubmittedReportPdfDocument)
//            ->addDocument($this->resubmittedSupportingDocumentWithResubmission);
//
//        // Additional docs for resubmission
//        $this->resubmittedReportAdditionalSubmission = (new ReportSubmission($this->firstReport, $this->user))
//            ->setUuid(null);
//
//        $this->supportingDocumentAfterResubmission = (new Document($this->firstReport))
//            ->setReportSubmission($this->resubmittedReportAdditionalSubmission)
//            ->setFileName('supporting-document-additional-after-resub.pdf')
//            ->setStorageReference('storage-ref-supporting-additional-after-resub.pdf')
//            ->setIsReportPdf(false)
//            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
//
//        $this->resubmittedReportAdditionalSubmission
//            ->addDocument($this->supportingDocumentAfterResubmission);
//
//        // NDR
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
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);

        $this->ndrSubmission
            ->addDocument($this->ndrReportPdfDocument);
//
//        // Second report
        $this->secondReport = (new Report(
            $this->client,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            $this->thirdJulyAm,
            $this->thirdJulyAm->add(new DateInterval('P364D')))
        )->setSubmitDate($this->thirdJulyPm);
//
//        // Initial report PDF submission with supporting docs
//        $this->secondReportSubmission = (new ReportSubmission($this->secondReport, $this->user))
//            ->setUuid('zzz-999-zzz-999');
//
//        $this->secondReportPdfDocument = (new Document($this->secondReport))
//            ->setReportSubmission($this->secondReportSubmission)
//            ->setFileName('second-report.pdf')
//            ->setStorageReference('storage-ref-second-report.pdf')
//            ->setIsReportPdf(true)
//            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
//
//        $this->secondSupportingDocument = (new Document($this->secondReport))
//            ->setReportSubmission($this->secondReportSubmission)
//            ->setFileName('second-supporting-document.pdf')
//            ->setStorageReference('storage-ref-second-supporting.pdf')
//            ->setIsReportPdf(false)
//            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
//
//        $this->secondReportSubmission
//            ->addDocument($this->secondReportPdfDocument)
//            ->addDocument($this->secondSupportingDocument);
    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    /**
     * @test
     */
    public function getQueuedDocumentsAndSetToInProgress()
    {
        $this->ensureUserAndClientArePersisted()
            ->ensureReportsArePersisted()
            ->ensureFirstReportWithSupportingDocHaveBeenSubmitted();

        $this->entityManager->flush();

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress('100');

        $reportPdf = $this->documentRepository->find($this->firstReportPdfDocument->getId());
        $supportingDocument = $this->documentRepository->find($this->firstSupportingDocument->getId());

        $this->entityManager->refresh($reportPdf);
        $this->entityManager->refresh($supportingDocument);

        $this->assertDataMatchesEntity($documents, $reportPdf, $this->client, $this->firstReportSubmission, $this->firstReport);
        $this->assertDataMatchesEntity($documents, $supportingDocument, $this->client, $this->firstReportSubmission, $this->firstReport);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $reportPdf->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $supportingDocument->getSynchronisationStatus());
    }

    /**
     * @test
     */
    public function getQueuedDocumentsAndSetToInProgress_supporting_document_uses_submission_uuid()
    {
        $this->ensureUserAndClientArePersisted()
            ->ensureReportsArePersisted()
            ->ensureFirstReportWithSupportingDocHaveBeenSubmitted()
            ->ensureFirstReportHasSynced();

        $this->entityManager->flush();

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress('100');

        self::assertEquals($this->firstReportSubmission->getUuid(), $documents[$this->firstSupportingDocument->getId()]['report_submission_uuid']);
    }

    /**
     * @test
     */
    public function getQueuedDocumentsAndSetToInProgress_supportsNdrs()
    {
        $this->ensureUserAndClientArePersisted()
            ->ensureNdrHasBeenSubmitted();

        $this->entityManager->flush();

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
        $this->ensureUserAndClientArePersisted()
            ->ensureReportsArePersisted()
            ->ensureFirstReportWithSupportingDocHaveBeenSubmitted()
            ->ensureFirstReportHasSynced()
            ->ensureFirstSupportingDocHasSynced()
            ->ensureFirstReportAdditionalDocsAreSubmitted();

        $this->entityManager->flush();

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        self::assertEquals($this->firstReportSubmission->getUuid(), $documents[$this->additionalSupportingDocumentAfterSubmission->getId()]['report_submission_uuid']);
        self::assertEquals($this->additionalReportSubmission->getId(), $documents[$this->additionalSupportingDocumentAfterSubmission->getId()]['report_submission_id']);
    }

    /**
     * @test
     */
    public function resubmissionsDoNotHaveOriginalSubmissionUUIDOnSubmission()
    {
        $this->ensureResubmissionReportAndDocAreSubmitted();

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        $resubmitReportPdf = $this->documentRepository->find($this->resubmittedReportPdfDocument->getId());
        $supportingDocumentWithResubmission = $this->documentRepository->find($this->resubmittedSupportingDocumentWithResubmission->getId());

        $this->entityManager->refresh($resubmitReportPdf);
        $this->entityManager->refresh($supportingDocumentWithResubmission);

        self::assertEquals(null, $documents[$resubmitReportPdf->getId()]['report_submission_uuid']);
        self::assertEquals($this->resubmittedReportSubmission->getId(), $documents[$resubmitReportPdf->getId()]['report_submission_id']);
        self::assertEquals(null, $documents[$supportingDocumentWithResubmission->getId()]['report_submission_uuid']);
        self::assertEquals($this->resubmittedReportSubmission->getId(), $documents[$supportingDocumentWithResubmission->getId()]['report_submission_id']);
    }

    /**
     * @test
     */
    public function additionalDocsOnResubmissionsUseResubmissionUUID()
    {
        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        self::assertEquals($this->resubmittedReportSubmission->getUuid(), $documents[$this->supportingDocumentAfterResubmission->getId()]['report_submission_uuid']);
    }

    /** @test */
    public function updateSupportingDocumentStatusByReportSubmissionIds()
    {
        $this->firstReportPdfDocument->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
        $this->persistEntities();

        $updatedDocumentsCount = $this->documentRepository
            ->updateSupportingDocumentStatusByReportSubmissionIds(
                [$this->firstReportSubmission->getId(), $this->additionalReportSubmission->getId()],
                'An error message'
            );

        $this->refreshDocumentEntities();

        $this->assertEquals(3, $updatedDocumentsCount);

        foreach([$this->firstSupportingDocument, $this->supportingDocumentAfterSubmission] as $doc) {
            self::assertEquals(Document::SYNC_STATUS_PERMANENT_ERROR, $doc->getSynchronisationStatus());
            self::assertEquals('An error message', $doc->getSynchronisationError());
        }

        self::assertEquals(Document::SYNC_STATUS_PERMANENT_ERROR, $this->firstReportPdfDocument->getSynchronisationStatus());
        self::assertEquals(null, $this->firstReportPdfDocument->getSynchronisationError());
    }

    /** @test */
    public function supportsWhenDocumentsForMoreThanOneReportAreQueued()
    {
        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress('100');

        $reportPdf = $this->documentRepository->find($this->secondReportPdfDocument->getId());
        $supportingDocument = $this->documentRepository->find($this->secondSupportingDocument->getId());

        $this->entityManager->refresh($reportPdf);
        $this->entityManager->refresh($supportingDocument);

        $this->assertDataMatchesEntity($documents, $reportPdf, $this->client, $this->secondReportSubmission, $this->secondReport);
        $this->assertDataMatchesEntity($documents, $supportingDocument, $this->client, $this->secondReportSubmission, $this->secondReport);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $reportPdf->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $supportingDocument->getSynchronisationStatus());
    }

    /** @test */
    public function documentLimitsAreRespected()
    {
        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress('2');

        self::assertEquals(2, count($documents));
    }

    /** @test */
    public function returnsAdditionalDocsWhenFirstBatchHaveSynced()
    {
        $this->firstReportPdfDocument->setSynchronisationStatus('SUCCESS');
        $this->firstSupportingDocument->setSynchronisationStatus('SUCCESS');

        $this->entityManager->persist($this->firstReportPdfDocument);
        $this->entityManager->persist($this->firstSupportingDocument);
        $this->entityManager->flush();

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress('100');

        $additionalSupportingDoc = $this->documentRepository->find($this->supportingDocumentAfterSubmission->getId());
        $this->entityManager->refresh($additionalSupportingDoc);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $additionalSupportingDoc->getSynchronisationStatus());
        self::assertEquals($this->firstReportSubmission->getUuid(), $documents[$this->supportingDocumentAfterSubmission->getId()]['report_submission_uuid']);
    }

    private function persistEntities()
    {
        $this->user->setEmail(sprintf('test-user%s%s@test.com', $this->uniq, rand(0, 100000)));

        foreach ($this->entities as $entity) {
            $this->entityManager->persist($entity);
        }

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
        $this->entityManager->refresh($this->firstReportPdfDocument);
        $this->entityManager->refresh($this->firstSupportingDocument);
        $this->entityManager->refresh($this->supportingDocumentAfterSubmission);
    }

    private function ensureUserAndClientArePersisted()
    {
        $this->user->setEmail(sprintf('test-user%s%s@test.com', $this->uniq, rand(0, 100000)));

        $this->entityManager->persist($this->user);
        $this->entityManager->persist($this->client);

        return $this;
    }

    private function ensureReportsArePersisted()
    {
        $this->entityManager->persist($this->firstReport);
        $this->entityManager->persist($this->secondReport);

        return $this;
    }

    private function ensureFirstReportWithSupportingDocHaveBeenSubmitted()
    {
        $this->firstReportSubmission
            ->addDocument($this->firstReportPdfDocument)
            ->addDocument($this->firstSupportingDocument);

        $this->firstReportPdfDocument
            ->setReportSubmission($this->firstReportSubmission);

        $this->firstSupportingDocument
            ->setReportSubmission($this->firstReportSubmission);

        $this->entityManager->persist($this->firstSupportingDocument);
        $this->entityManager->persist($this->firstReportPdfDocument);
        $this->entityManager->persist($this->firstReportSubmission);

        return $this;
    }

    private function ensureNdrHasBeenSubmitted()
    {
        $this->entityManager->persist($this->ndr);
        $this->entityManager->persist($this->ndrReportPdfDocument);
        $this->entityManager->persist($this->ndrSubmission);

        return $this;
    }

    private function ensureFirstReportHasSynced()
    {
        $this->entityManager->persist($this->firstReportSubmission->setUuid('abc-123-abc-123'));

        $this->firstReportPdfDocument->setSynchronisationStatus('SUCCESS');
        $this->entityManager->persist($this->firstReportPdfDocument);

        return $this;
    }

    private function ensureFirstSupportingDocHasSynced()
    {
        $this->entityManager->persist($this->firstReportSubmission->setUuid('abc-123-abc-123'));

        $this->firstSupportingDocument->setSynchronisationStatus('SUCCESS');
        $this->entityManager->persist($this->firstSupportingDocument);

        return $this;
    }

    private function ensureFirstReportAdditionalDocsAreSubmitted()
    {
        $this->additionalReportSubmission
            ->addDocument($this->additionalSupportingDocumentAfterSubmission);

        $this->additionalSupportingDocumentAfterSubmission
            ->setReportSubmission($this->additionalReportSubmission);

        $this->entityManager->persist($this->additionalSupportingDocumentAfterSubmission);
        $this->entityManager->persist($this->additionalReportSubmission);

        return $this;
    }

    private function ensureFirstReportAdditionalDocHasSynced()
    {
        $this->entityManager->persist(
            $this->additionalSupportingDocumentAfterSubmission->setSynchronisationStatus('SUCCESS')
        );
    }

    private function ensureResubmissionReportAndDocAreSubmitted()
    {
        $this->entityManager->persist($this->resubmittedReportPdfDocument);
        $this->entityManager->persist($this->resubmittedSupportingDocumentWithResubmission);
        $this->entityManager->persist($this->resubmittedReportSubmission);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
