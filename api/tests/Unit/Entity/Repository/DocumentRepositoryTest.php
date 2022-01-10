<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\ReportInterface;
use App\Entity\User;
use App\Repository\DocumentRepository;
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

    /** @var DocumentRepository */
    private $documentRepository;

    /** @var DateTime */
    private $firstJulyAm;
    private $firstJulyPm;
    private $secondJulyAm;
    private $secondJulyPm;
    private $thirdJulyAm;
    private $thirdJulyPm;

    /**
     * @test
     */
    public function getQueuedDocumentsAndSetToInProgress()
    {
        [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        $this->entityManager->refresh($reportPdfDoc);
        $this->entityManager->refresh($supportingDoc);

        $this->assertDataMatchesEntity($documents, $reportPdfDoc, $client, $reportSubmission, $report);
        $this->assertDataMatchesEntity($documents, $supportingDoc, $client, $reportSubmission, $report);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $reportPdfDoc->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $supportingDoc->getSynchronisationStatus());
    }

    private function createAndSubmitReportWithSupportingDoc(DateTime $submittedOn)
    {
        $client = $this->generateAndPersistClient('abc-123');
        $report = $this->generateAndPersistReport($client, false);
        $reportPdfDoc = $this->generateAndPersistDocument($report, true, 'QUEUED', $this->firstJulyPm, false);
        $supportingDoc = $this->generateAndPersistDocument($report, false, 'QUEUED', $this->firstJulyAm, false);

        $reportSubmission = $this->submitReport($report, $submittedOn, $reportPdfDoc, $supportingDoc);

        $this->entityManager->flush();

        return [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission];
    }

    private function generateAndPersistClient(string $caseNumber)
    {
        $client = (new Client())->setCaseNumber($caseNumber);

        $this->entityManager->persist($client);

        return $client;
    }

    private function generateAndPersistReport(Client $client, bool $isNdr)
    {
        if ($isNdr) {
            $report = (new Ndr($client))->setStartDate($this->firstJulyAm);
        } else {
            $report = (new Report(
                $client,
                Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
                $this->firstJulyAm,
                $this->firstJulyAm->add(new DateInterval('P364D'))
            )
            );
        }

        $this->entityManager->persist($report);

        return $report;
    }

    private function generateAndPersistDocument(ReportInterface $report, bool $isReportPdf, string $syncStatus, DateTime $createdOn, bool $isResubmission)
    {
        $fileName = $isReportPdf ? 'report' : 'supporting-document';
        $storageRef = $isReportPdf ? 'storage-ref-report' : 'storage-ref-supporting-document';

        $fileName .= $isResubmission ? '-resubmission.pdf' : '.pdf';
        $storageRef .= $isResubmission ? '-resubmission.pdf' : '.pdf';

        $doc = (new Document($report))
            ->setFileName($fileName)
            ->setStorageReference($storageRef)
            ->setIsReportPdf($isReportPdf)
            ->setSynchronisationStatus($syncStatus)
            ->setCreatedOn($createdOn);

        $this->entityManager->persist($doc);

        return $doc;
    }

    private function submitReport(ReportInterface $report, DateTime $submittedOn, Document $reportPdf, ?Document $supportingDocument)
    {
        $report->setSubmitDate($submittedOn);

        $reportSubmission = $this->generateAndPersistReportSubmission($report, $submittedOn);
        $reportSubmission->addDocument($reportPdf);

        $reportPdf->setReportSubmission($reportSubmission);

        if ($supportingDocument) {
            $reportSubmission->addDocument($supportingDocument);
            $supportingDocument->setReportSubmission($reportSubmission);

            $this->entityManager->persist($supportingDocument);
        }

        $this->entityManager->persist($reportPdf);
        $this->entityManager->persist($reportSubmission);

        return $reportSubmission;
    }

    private function generateAndPersistReportSubmission(ReportInterface $report, DateTime $createdOn)
    {
        $submission = (new ReportSubmission($report, $this->generateAndPersistUser()))->setCreatedOn($createdOn);

        $this->entityManager->persist($submission);

        return $submission;
    }

    private function generateAndPersistUser()
    {
        $user = (new User())
            ->setFirstname('Test')
            ->setLastname('User')
            ->setPassword('password123');

        $datePostFix = (string) (new DateTime())->getTimestamp();
        $user->setEmail(sprintf('test-user%s%s@test.com', $datePostFix, rand(0, 100000)));

        $this->entityManager->persist($user);

        return $user;
    }

    /**
     * @param Report|Ndr $report
     */
    private function assertDataMatchesEntity(
        array $documents,
        Document $document,
        Client $client,
        ReportSubmission $submission,
                         $report
    ) {
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

    /**
     * @test
     */
    public function getQueuedDocumentsAndSetToInProgressSupportingDocumentUsesSubmissionUuid()
    {
        [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->syncDocuments([$reportPdfDoc], $reportSubmission, 'abc-123-abc-123');

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        self::assertEquals('abc-123-abc-123', $documents[$supportingDoc->getId()]['report_submission_uuid']);
    }

    private function syncDocuments(array $documents, ?ReportSubmission $submission, ?string $uuid)
    {
        if ($submission) {
            $this->entityManager->persist($submission->setUuid($uuid));
        }

        foreach ($documents as $document) {
            $document->setSynchronisationStatus('SUCCESS');
            $this->entityManager->persist($document);
        }

        $this->entityManager->flush();
    }

    /**
     * @test
     */
    public function getQueuedDocumentsAndSetToInProgressSupportsNdrs()
    {
        [$client, $ndr, $reportPdfDoc, $reportSubmission] = $this->createAndSubmitNdr();

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        $this->assertDataMatchesEntity($documents, $reportPdfDoc, $client, $reportSubmission, $ndr);

        $this->entityManager->refresh($reportPdfDoc);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $reportPdfDoc->getSynchronisationStatus());
    }

    private function createAndSubmitNdr()
    {
        $client = $this->generateAndPersistClient('abc-123');
        $ndr = $this->generateAndPersistReport($client, true);
        $reportPdfDoc = $this->generateAndPersistDocument($ndr, true, 'QUEUED', $this->firstJulyAm, false);

        $reportSubmission = $this->submitReport($ndr, $this->secondJulyPm, $reportPdfDoc, null);

        $this->entityManager->flush();

        return [$client, $ndr, $reportPdfDoc, $reportSubmission];
    }

    /**
     * @test
     */
    public function additionalDocumentsSubmissionsUseOriginalSubmissionUUID()
    {
        [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->syncDocuments([$reportPdfDoc, $supportingDoc], $reportSubmission, null);
        [$additionalSubmission, $additionalSupportingDoc] = $this->createAndSubmitAdditionalDocuments($report, $this->thirdJulyAm);

        $this->entityManager->flush();

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        $this->entityManager->refresh($additionalSupportingDoc);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $additionalSupportingDoc->getSynchronisationStatus());
        self::assertEquals($reportSubmission->getUuid(), $documents[$additionalSupportingDoc->getId()]['report_submission_uuid']);
        self::assertEquals($additionalSubmission->getId(), $documents[$additionalSupportingDoc->getId()]['report_submission_id']);
    }

    private function createAndSubmitAdditionalDocuments(ReportInterface $report, DateTime $submittedOn)
    {
        $additionalSubmission = $this->generateAndPersistReportSubmission($report, $submittedOn);
        $additionalSupportingDoc = $this->generateAndPersistDocument($report, false, 'QUEUED', $submittedOn, false);

        $additionalSubmission->addDocument($additionalSupportingDoc);
        $additionalSupportingDoc->setReportSubmission($additionalSubmission);

        $this->entityManager->persist($additionalSupportingDoc);
        $this->entityManager->persist($additionalSubmission);

        return [$additionalSubmission, $additionalSupportingDoc];
    }

    /**
     * @test
     */
    public function resubmissionsDoNotHaveOriginalSubmissionUUIDOnSubmission()
    {
        [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->syncDocuments([$reportPdfDoc, $supportingDoc], $reportSubmission, 'abc-123-abc-123');
        [$resubmissionReportPdfDoc, $resubmissionSupportingDoc, $reportResubmission] = $this->createAndSubmitResubmissionWithSupportingDoc($report, $this->secondJulyAm);

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        $this->entityManager->refresh($resubmissionReportPdfDoc);
        $this->entityManager->refresh($resubmissionSupportingDoc);

        self::assertEquals(null, $documents[$resubmissionReportPdfDoc->getId()]['report_submission_uuid']);
        self::assertEquals($reportResubmission->getId(), $documents[$resubmissionReportPdfDoc->getId()]['report_submission_id']);
        self::assertEquals(null, $documents[$resubmissionSupportingDoc->getId()]['report_submission_uuid']);
        self::assertEquals($reportResubmission->getId(), $documents[$resubmissionSupportingDoc->getId()]['report_submission_id']);
    }

    private function createAndSubmitResubmissionWithSupportingDoc(ReportInterface $report, DateTime $submittedOn)
    {
        $resubmissionReportPdfDoc = $this->generateAndPersistDocument($report, true, 'QUEUED', $this->secondJulyAm, true);
        $resubmissionSupportingDoc = $this->generateAndPersistDocument($report, false, 'QUEUED', $this->secondJulyAm, true);

        $reportResubmission = $this->submitReport($report, $submittedOn, $resubmissionReportPdfDoc, $resubmissionSupportingDoc);

        $this->entityManager->flush();

        return [$resubmissionReportPdfDoc, $resubmissionSupportingDoc, $reportResubmission];
    }

    /**
     * @test
     */
    public function additionalDocsOnResubmissionsUseResubmissionUUID()
    {
        [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);
        $this->syncDocuments([$reportPdfDoc, $supportingDoc], $reportSubmission, 'abc-123-abc-123');

        [$resubmissionReportPdfDoc, $resubmissionSupportingDoc, $reportResubmission] = $this->createAndSubmitResubmissionWithSupportingDoc($report, $this->thirdJulyAm);
        $this->syncDocuments([$resubmissionReportPdfDoc, $resubmissionSupportingDoc], $reportResubmission, 'def-456-def-456');

        [$additionalSubmission, $additionalSupportingDoc] = $this->createAndSubmitAdditionalDocuments($report, $this->thirdJulyPm);

        $this->entityManager->flush();

        $documents = $this->documentRepository->getQueuedDocumentsAndSetToInProgress('100');

        $this->syncDocuments([$additionalSupportingDoc], $additionalSubmission, 'def-456-def-456');

        $this->entityManager->refresh($additionalSubmission);
        $this->entityManager->refresh($additionalSupportingDoc);

        self::assertEquals($additionalSubmission->getUuid(), $documents[$additionalSupportingDoc->getId()]['report_submission_uuid']);
    }

    /** @test */
    public function updateSupportingDocumentStatusByReportSubmissionIds()
    {
        [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);
        [$client2, $report2, $reportPdfDoc2, $supportingDoc2, $reportSubmission2] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);

        $updatedDocumentsCount = $this->documentRepository
            ->updateSupportingDocumentStatusByReportSubmissionIds(
                [$reportSubmission->getId(), $reportSubmission2->getId()],
                'An error message'
            );

        $this->entityManager->refresh($supportingDoc);
        $this->entityManager->refresh($supportingDoc2);
        $this->entityManager->refresh($reportPdfDoc);
        $this->entityManager->refresh($reportPdfDoc2);

        $this->assertEquals(2, $updatedDocumentsCount);

        foreach ([$supportingDoc, $supportingDoc2] as $doc) {
            self::assertEquals(Document::SYNC_STATUS_PERMANENT_ERROR, $doc->getSynchronisationStatus());
            self::assertEquals('An error message', $doc->getSynchronisationError());
        }

        foreach ([$reportPdfDoc, $reportPdfDoc2] as $doc) {
            self::assertEquals(Document::SYNC_STATUS_QUEUED, $doc->getSynchronisationStatus());
            self::assertEquals(null, $doc->getSynchronisationError());
        }
    }

    /** @test */
    public function supportsWhenDocumentsForMoreThanOneReportAreQueued()
    {
        [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);
        [$client2, $report2, $reportPdfDoc2, $supportingDoc2, $reportSubmission2] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyPm);

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress('100');

        $this->entityManager->refresh($reportPdfDoc);
        $this->entityManager->refresh($supportingDoc);
        $this->entityManager->refresh($reportPdfDoc2);
        $this->entityManager->refresh($supportingDoc2);

        $this->assertDataMatchesEntity($documents, $reportPdfDoc, $client, $reportSubmission, $report);
        $this->assertDataMatchesEntity($documents, $supportingDoc, $client, $reportSubmission, $report);
        $this->assertDataMatchesEntity($documents, $reportPdfDoc2, $client2, $reportSubmission2, $report2);
        $this->assertDataMatchesEntity($documents, $supportingDoc2, $client2, $reportSubmission2, $report2);

        foreach ([$reportPdfDoc, $supportingDoc, $reportPdfDoc, $supportingDoc] as $doc) {
            self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $doc->getSynchronisationStatus());
        }
    }

    /** @test */
    public function documentLimitsAreRespected()
    {
        $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->createAndSubmitReportWithSupportingDoc($this->firstJulyPm);

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress('2');

        self::assertEquals(2, count($documents));
    }

    /** @test */
    public function documentsAreOrderedByIsReportPdf()
    {
        [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);

        foreach (range(1, 5) as $index) {
            $this->createAndSubmitAdditionalDocuments($report, $this->firstJulyPm);
        }

        [$client2, $report2, $reportPdfDoc2, $supportingDoc2, $reportSubmission2] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);

        $this->createAndSubmitAdditionalDocuments($report, $this->secondJulyPm);

        $documents = $this->documentRepository
            ->getQueuedDocumentsAndSetToInProgress('5');

        $reportPdf1Returned = false;
        $reportPdf2Returned = false;

        foreach ($documents as $document) {
            if ($document['document_id'] === $reportPdfDoc->getId()) {
                $reportPdf1Returned = true;
            }

            if ($document['document_id'] === $reportPdfDoc2->getId()) {
                $reportPdf2Returned = true;
            }
        }

        self::assertTrue($reportPdf1Returned, '$reportPdf1Returned was not returned');
        self::assertTrue($reportPdf2Returned, '$reportPdf2Returned was not returned');
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->documentRepository = $this->entityManager
            ->getRepository(Document::class);

        $this->purgeDatabase();

        $this->firstJulyAm = DateTime::createFromFormat('d/m/Y', '01/07/2020', new DateTimeZone('UTC'));
        $this->firstJulyPm = clone $this->firstJulyAm->add(new DateInterval('PT20H'));
        $this->secondJulyAm = DateTime::createFromFormat('d/m/Y', '02/07/2020', new DateTimeZone('UTC'));
        $this->secondJulyPm = clone $this->secondJulyAm->add(new DateInterval('PT20H'));
        $this->thirdJulyAm = DateTime::createFromFormat('d/m/Y', '03/07/2020', new DateTimeZone('UTC'));
        $this->thirdJulyPm = clone $this->thirdJulyAm->add(new DateInterval('PT20H'));
    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
