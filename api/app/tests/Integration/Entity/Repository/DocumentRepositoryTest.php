<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Entity\Repository;

use Tests\OPG\Digideps\Backend\Integration\ApiTestTrait;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DocumentRepositoryTest extends KernelTestCase
{
    use ApiTestTrait;

    private \DateTime $firstJulyAm;
    private \DateTime $firstJulyPm;
    private \DateTime $secondJulyAm;
    private \DateTime $secondJulyPm;
    private \DateTime $thirdJulyAm;
    private \DateTime $thirdJulyPm;

    private static DocumentRepository $sut;

    protected function setUp(): void
    {
        parent::setUp();

        self::configureTest();

        self::purgeDatabase();

        /** @var DocumentRepository $repo */
        $repo = self::$entityManager->getRepository(Document::class);
        self::$sut = $repo;

        $this->firstJulyAm = \DateTime::createFromFormat('d/m/Y', '01/07/2020', new \DateTimeZone('UTC'));
        $this->firstJulyPm = clone $this->firstJulyAm->add(new \DateInterval('PT20H'));
        $this->secondJulyAm = \DateTime::createFromFormat('d/m/Y', '02/07/2020', new \DateTimeZone('UTC'));
        $this->secondJulyPm = clone $this->secondJulyAm->add(new \DateInterval('PT20H'));
        $this->thirdJulyAm = \DateTime::createFromFormat('d/m/Y', '03/07/2020', new \DateTimeZone('UTC'));
        $this->thirdJulyPm = clone $this->thirdJulyAm->add(new \DateInterval('PT20H'));
    }

    /**
     * @return array{Client, Report, Document, Document, ReportSubmission}
     */
    private function createAndSubmitReportWithSupportingDoc(\DateTime $submittedOn): array
    {
        $client = $this->generateAndPersistClient('abc-123');
        $report = $this->generateAndPersistReport($client);
        $reportPdfDoc = $this->generateAndPersistDocument($report, true, 'QUEUED', $this->firstJulyPm, false);
        $supportingDoc = $this->generateAndPersistDocument($report, false, 'QUEUED', $this->firstJulyAm, false);

        $reportSubmission = $this->submitReport($report, $submittedOn, $reportPdfDoc, $supportingDoc);

        self::$entityManager->flush();

        return [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission];
    }

    private function generateAndPersistClient(string $caseNumber): Client
    {
        $client = new Client()->setCaseNumber($caseNumber);

        self::$entityManager->persist($client);

        return $client;
    }

    private function generateAndPersistReport(Client $client): Report
    {
        $report = (new Report(
            $client,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            $this->firstJulyAm,
            $this->firstJulyAm->add(new \DateInterval('P364D'))
        ));

        self::$entityManager->persist($report);

        return $report;
    }

    private function generateAndPersistDocument(Report $report, bool $isReportPdf, string $syncStatus, \DateTime $createdOn, bool $isResubmission): Document
    {
        $fileName = $isReportPdf ? 'report' : 'supporting-document';
        $storageRef = $isReportPdf ? 'storage-ref-report' : 'storage-ref-supporting-document';

        $fileName .= $isResubmission ? '-resubmission.pdf' : '.pdf';
        $storageRef .= $isResubmission ? '-resubmission.pdf' : '.pdf';

        $doc = new Document($report)
            ->setFileName($fileName)
            ->setStorageReference($storageRef)
            ->setIsReportPdf($isReportPdf)
            ->setSynchronisationStatus($syncStatus)
            ->setCreatedOn($createdOn);

        self::$entityManager->persist($doc);

        return $doc;
    }

    private function submitReport(Report $report, \DateTime $submittedOn, Document $reportPdf, ?Document $supportingDocument): ReportSubmission
    {
        $report->setSubmitDate($submittedOn);
        $reportSubmission = $this->generateAndPersistReportSubmission($report, $submittedOn);
        $reportSubmission->addDocument($reportPdf);

        $reportPdf->setReportSubmission($reportSubmission);

        if ($supportingDocument) {
            $reportSubmission->addDocument($supportingDocument);
            $supportingDocument->setReportSubmission($reportSubmission);

            self::$entityManager->persist($supportingDocument);
        }

        self::$entityManager->persist($reportPdf);
        self::$entityManager->persist($reportSubmission);

        return $reportSubmission;
    }

    private function generateAndPersistReportSubmission(Report $report, \DateTime $createdOn): ReportSubmission
    {
        $submission = new ReportSubmission($report, $this->generateAndPersistUser())->setCreatedOn($createdOn);

        self::$entityManager->persist($submission);

        return $submission;
    }

    private function generateAndPersistUser(): User
    {
        $user = new User()
            ->setFirstname('Test')
            ->setLastname('User')
            ->setPassword('password123');

        $datePostFix = (string) new \DateTime()->getTimestamp();
        $user->setEmail(sprintf('test-user%s%s@test.com', $datePostFix, rand(0, 100000)));

        self::$entityManager->persist($user);

        return $user;
    }

    private function assertDataMatchesEntity(
        array $documents,
        Document $document,
        Client $client,
        ReportSubmission $submission,
        Report $report,
    ): void {
        $docId = $document->getId();

        self::assertEquals($document->getId(), $documents[$docId]['document_id']);
        self::assertEquals($submission->getId(), $documents[$docId]['report_submission_id']);
        self::assertEquals($client->getCaseNumber(), $documents[$docId]['case_number']);
        self::assertEquals($document->isReportPdf(), $documents[$docId]['is_report_pdf']);
        self::assertEquals($document->getStorageReference(), $documents[$docId]['storage_reference']);
        self::assertEquals($report->getStartDate()->format('Y-m-d'), $documents[$docId]['report_start_date']);
        self::assertEquals($report->getSubmitDate()?->format('Y-m-d H:i:s'), $documents[$docId]['report_submit_date']);
        self::assertEquals($submission->getUuid(), $documents[$docId]['report_submission_uuid']);

        if ($report instanceof Report) {
            self::assertEquals($report->getEndDate()->format('Y-m-d'), $documents[$docId]['report_end_date']);
            self::assertEquals($report->getType(), $documents[$docId]['report_type']);
        }
    }

    private function createFailedDocumentSubmission($status, $createdOn, $caseNumber, $archived): void
    {
        $client = $this->generateAndPersistClient('abc-123-' . $caseNumber);
        $report = $this->generateAndPersistReport($client);
        $reportPdfDoc = $this->generateAndPersistDocument($report, true, $status, $this->firstJulyAm, false);
        $supportingDoc = $this->generateAndPersistDocument($report, false, $status, $this->firstJulyAm, false);
        $reportSubmission = $this->submitReport($report, $this->firstJulyPm, $reportPdfDoc, $supportingDoc);
        $reportSubmission->setCreatedOn($createdOn);
        $reportSubmission->setArchived($archived);
        self::$entityManager->persist($reportSubmission);
        self::$entityManager->flush();
    }

    /**
     * @param Document[] $documents
     */
    private function syncDocuments(array $documents, ?ReportSubmission $submission, ?string $uuid): void
    {
        if ($submission) {
            self::$entityManager->persist($submission->setUuid($uuid));
        }

        foreach ($documents as $document) {
            $document->setSynchronisationStatus('SUCCESS');
            self::$entityManager->persist($document);
        }

        self::$entityManager->flush();
    }

    /**
     * @return array{ReportSubmission, Document}
     */
    private function createAndSubmitAdditionalDocuments(Report $report, \DateTime $submittedOn): array
    {
        $additionalSubmission = $this->generateAndPersistReportSubmission($report, $submittedOn);
        $additionalSupportingDoc = $this->generateAndPersistDocument($report, false, 'QUEUED', $submittedOn, false);

        $additionalSubmission->addDocument($additionalSupportingDoc);
        $additionalSupportingDoc->setReportSubmission($additionalSubmission);

        self::$entityManager->persist($additionalSupportingDoc);
        self::$entityManager->persist($additionalSubmission);

        return [$additionalSubmission, $additionalSupportingDoc];
    }

    /**
     * @return array{Document, Document, ReportSubmission}
     */
    private function createAndSubmitResubmissionWithSupportingDoc(Report $report, \DateTime $submittedOn): array
    {
        $resubmissionReportPdfDoc = $this->generateAndPersistDocument($report, true, 'QUEUED', $this->secondJulyAm, true);
        $resubmissionSupportingDoc = $this->generateAndPersistDocument($report, false, 'QUEUED', $this->secondJulyAm, true);

        $reportResubmission = $this->submitReport($report, $submittedOn, $resubmissionReportPdfDoc, $resubmissionSupportingDoc);

        self::$entityManager->flush();

        return [$resubmissionReportPdfDoc, $resubmissionSupportingDoc, $reportResubmission];
    }

    public function testGetQueuedDocumentsAndSetToInProgress(): void
    {
        [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress(100);

        self::$entityManager->refresh($reportPdfDoc);
        self::$entityManager->refresh($supportingDoc);

        $this->assertDataMatchesEntity($documents, $reportPdfDoc, $client, $reportSubmission, $report);
        $this->assertDataMatchesEntity($documents, $supportingDoc, $client, $reportSubmission, $report);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $reportPdfDoc->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $supportingDoc->getSynchronisationStatus());
    }

    public function testGetResubmittableErrorDocumentsAndSetToQueued(): void
    {
        $currentDateTime = new \DateTime('now');
        $twoDaysAgoDateTime = new \DateTime('now - 2 days');
        $lastYearDateTime = new \DateTime('now -1 year');

        // -- documents with recognised permanent errors which will be queued
        [$_, $_, $willQueue1, $willQueue2, $_] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);

        $willQueue1->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
        $willQueue1->setSynchronisationError('Document failed to sync after 4 attempts');

        $willQueue2->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
        $willQueue2->setSynchronisationError('Report PDF failed to sync');

        // --- documents are marked as sync in progress, but submission is really old, so will be queued anyway
        [$_, $_, $willQueue3, $willQueue4, $reportSub1] = $this->createAndSubmitReportWithSupportingDoc($lastYearDateTime);
        $willQueue3->setSynchronisationStatus(Document::SYNC_STATUS_IN_PROGRESS);
        $willQueue4->setSynchronisationStatus(Document::SYNC_STATUS_IN_PROGRESS);

        // --- document with unrecognised permanent errors where submission is < 3 days ago
        // report PDF has permanent error status and the error message doesn't match any in the SQL query;
        // it is still retried because the submission was less than 3 days ago
        [$_, $_, $willQueue5, $willQueue6, $reportSub2] = $this->createAndSubmitReportWithSupportingDoc($twoDaysAgoDateTime);
        $willQueue5->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
        $willQueue6->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);

        // --- documents which will not be queued because they already have been sync'ed, or submission is too old
        [$_, $_, $willNotQueue1, $willNotQueue2, $reportSub3] = $this->createAndSubmitReportWithSupportingDoc($lastYearDateTime);

        // sync already succeeded so will not be queued
        $willNotQueue1->setSynchronisationStatus(Document::SYNC_STATUS_SUCCESS);

        // will not queue as error message is not recognised AND submission is too old
        $willNotQueue2->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
        $willNotQueue2->setSynchronisationError('Unrecognised error');

        // --- documents will not queue as sync already in progress
        [$_, $_, $willNotQueue3, $willNotQueue4, $reportSub4] = $this->createAndSubmitReportWithSupportingDoc($currentDateTime);
        $willNotQueue3->setSynchronisationStatus(Document::SYNC_STATUS_IN_PROGRESS);
        $willNotQueue4->setSynchronisationStatus(Document::SYNC_STATUS_IN_PROGRESS);

        self::$entityManager->persist($willQueue1);
        self::$entityManager->persist($willQueue2);
        self::$entityManager->persist($willQueue3);
        self::$entityManager->persist($willQueue4);
        self::$entityManager->persist($willQueue5);
        self::$entityManager->persist($willQueue6);
        self::$entityManager->persist($willNotQueue1);
        self::$entityManager->persist($willNotQueue2);
        self::$entityManager->persist($willNotQueue3);
        self::$entityManager->persist($willNotQueue4);

        self::$entityManager->persist($reportSub1);
        self::$entityManager->persist($reportSub2);
        self::$entityManager->persist($reportSub3);
        self::$entityManager->persist($reportSub4);

        self::$entityManager->flush();

        $documents = self::$sut->getResubmittableErrorDocumentsAndSetToQueued('100');

        self::$entityManager->refresh($willQueue1);
        self::assertEquals(Document::SYNC_STATUS_QUEUED, $willQueue1->getSynchronisationStatus());

        self::$entityManager->refresh($willQueue2);
        self::assertEquals(Document::SYNC_STATUS_QUEUED, $willQueue2->getSynchronisationStatus());

        self::$entityManager->refresh($willQueue3);
        self::assertEquals(Document::SYNC_STATUS_QUEUED, $willQueue3->getSynchronisationStatus());

        self::$entityManager->refresh($willQueue4);
        self::assertEquals(Document::SYNC_STATUS_QUEUED, $willQueue4->getSynchronisationStatus());

        self::$entityManager->refresh($willQueue5);
        self::assertEquals(Document::SYNC_STATUS_QUEUED, $willQueue5->getSynchronisationStatus());

        self::$entityManager->refresh($willQueue6);
        self::assertEquals(Document::SYNC_STATUS_QUEUED, $willQueue6->getSynchronisationStatus());

        self::$entityManager->refresh($willNotQueue1);
        self::assertEquals(Document::SYNC_STATUS_SUCCESS, $willNotQueue1->getSynchronisationStatus());

        self::$entityManager->refresh($willNotQueue2);
        self::assertEquals(Document::SYNC_STATUS_PERMANENT_ERROR, $willNotQueue2->getSynchronisationStatus());

        self::$entityManager->refresh($willNotQueue3);
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $willNotQueue3->getSynchronisationStatus());

        self::$entityManager->refresh($willNotQueue4);
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $willNotQueue4->getSynchronisationStatus());

        self::assertEquals(6, count($documents));
    }

    public function testLogFailedDocuments(): void
    {
        $currentDateTime = new \DateTime(); // Current date and time
        $tomorrow = $currentDateTime->modify('+1 day');

        // Tomorrow shouldn't count. Archived shouldn't count.
        $arguments = [
            ['QUEUED', $this->firstJulyPm, false],
            ['IN_PROGRESS', $this->firstJulyPm, false],
            ['TEMPORARY_ERROR', $this->firstJulyPm, false],
            ['PERMANENT_ERROR', $this->firstJulyPm, false],
            ['PERMANENT_ERROR', $this->firstJulyPm, true],
            ['QUEUED', $tomorrow, false],
            ['IN_PROGRESS', $tomorrow, false],
        ];

        foreach ($arguments as $index => $argument) {
            list($status, $date, $archived) = $argument;
            $id = $index + 1;
            $this->createFailedDocumentSubmission($status, $date, $id, $archived);
        }

        $result = self::$sut->logFailedDocuments();

        // 1 pdf and 1 supporting per submission.
        $this->assertEquals([
            'queued_over_1_hour' => 2,
            'in_progress_over_1_hour' => 2,
            'temporary_error_count' => 2,
            'permanent_error_count' => 2,
        ], $result);
    }

    public function testGetQueuedDocumentsAndSetToInProgressSupportingDocumentUsesSubmissionUuid(): void
    {
        /** @var Document $supportingDoc */
        [$_, $_, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->syncDocuments([$reportPdfDoc], $reportSubmission, 'abc-123-abc-123');

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress(100);
        $specificDoc = $documents[$supportingDoc->getId()];

        self::assertEquals('abc-123-abc-123', $specificDoc['report_submission_uuid']);
    }

    public function testAdditionalDocumentsSubmissionsUseOriginalSubmissionUUID(): void
    {
        [$_, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->syncDocuments([$reportPdfDoc, $supportingDoc], $reportSubmission, null);
        /**
         * @var ReportSubmission $additionalSubmission
         * @var Document $additionalSupportingDoc
         */
        [$additionalSubmission, $additionalSupportingDoc] = $this->createAndSubmitAdditionalDocuments($report, $this->thirdJulyAm);

        self::$entityManager->flush();

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress(100);
        $additionalDoc = $documents[$additionalSupportingDoc->getId()];

        self::$entityManager->refresh($additionalSupportingDoc);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $additionalSupportingDoc->getSynchronisationStatus());
        self::assertEquals($reportSubmission->getUuid(), $additionalDoc['report_submission_uuid']);
        self::assertEquals($additionalSubmission->getId(), $additionalDoc['report_submission_id']);
    }

    public function testResubmissionsDoNotHaveOriginalSubmissionUUIDOnSubmission(): void
    {
        [$_, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->syncDocuments([$reportPdfDoc, $supportingDoc], $reportSubmission, 'abc-123-abc-123');
        /**
         * @var Document $resubmissionReportPdfDoc
         * @var Document $resubmissionSupportingDoc
         * @var ReportSubmission $reportResubmission
         */
        [$resubmissionReportPdfDoc, $resubmissionSupportingDoc, $reportResubmission] = $this->createAndSubmitResubmissionWithSupportingDoc($report, $this->secondJulyAm);

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress(100);
        $resubmissionReportPdf = $documents[$resubmissionReportPdfDoc->getId()];
        $resubmissionSupport = $documents[$resubmissionSupportingDoc->getId()];

        self::$entityManager->refresh($resubmissionReportPdfDoc);
        self::$entityManager->refresh($resubmissionSupportingDoc);

        self::assertEquals(null, $resubmissionReportPdf['report_submission_uuid']);
        self::assertEquals($reportResubmission->getId(), $resubmissionReportPdf['report_submission_id']);
        self::assertEquals(null, $resubmissionSupport['report_submission_uuid']);
        self::assertEquals($reportResubmission->getId(), $resubmissionSupport['report_submission_id']);
    }

    public function testAdditionalDocsOnResubmissionsUseResubmissionUUID(): void
    {
        [$_, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);
        $this->syncDocuments([$reportPdfDoc, $supportingDoc], $reportSubmission, 'abc-123-abc-123');

        [$resubmissionReportPdfDoc, $resubmissionSupportingDoc, $reportResubmission] = $this->createAndSubmitResubmissionWithSupportingDoc($report, $this->thirdJulyAm);
        $this->syncDocuments([$resubmissionReportPdfDoc, $resubmissionSupportingDoc], $reportResubmission, 'def-456-def-456');

        /**
         * @var ReportSubmission $additionalSubmission,
         * @var Document $additionalSupportingDoc
         */
        [$additionalSubmission, $additionalSupportingDoc] = $this->createAndSubmitAdditionalDocuments($report, $this->thirdJulyPm);

        self::$entityManager->flush();

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress(100);
        $additionalDoc = $documents[$additionalSupportingDoc->getId()];

        $this->syncDocuments([$additionalSupportingDoc], $additionalSubmission, 'def-456-def-456');

        self::$entityManager->refresh($additionalSubmission);
        self::$entityManager->refresh($additionalSupportingDoc);

        self::assertEquals($additionalSubmission->getUuid(), $additionalDoc['report_submission_uuid']);
    }

    public function testUpdateSupportingDocumentStatusByReportSubmissionIds(): void
    {
        [$_, $_, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);
        [$_, $_, $reportPdfDoc2, $supportingDoc2, $reportSubmission2] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);

        $updatedDocumentsCount = self::$sut
            ->updateSupportingDocumentStatusByReportSubmissionIds(
                [$reportSubmission->getId(), $reportSubmission2->getId()],
                'An error message'
            );

        self::$entityManager->refresh($supportingDoc);
        self::$entityManager->refresh($supportingDoc2);
        self::$entityManager->refresh($reportPdfDoc);
        self::$entityManager->refresh($reportPdfDoc2);

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

    public function testSupportsWhenDocumentsForMoreThanOneReportAreQueued(): void
    {
        [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);
        [$client2, $report2, $reportPdfDoc2, $supportingDoc2, $reportSubmission2] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyPm);

        $documents = self::$sut
            ->getQueuedDocumentsAndSetToInProgress(100);

        self::$entityManager->refresh($reportPdfDoc);
        self::$entityManager->refresh($supportingDoc);
        self::$entityManager->refresh($reportPdfDoc2);
        self::$entityManager->refresh($supportingDoc2);

        $this->assertDataMatchesEntity($documents, $reportPdfDoc, $client, $reportSubmission, $report);
        $this->assertDataMatchesEntity($documents, $supportingDoc, $client, $reportSubmission, $report);
        $this->assertDataMatchesEntity($documents, $reportPdfDoc2, $client2, $reportSubmission2, $report2);
        $this->assertDataMatchesEntity($documents, $supportingDoc2, $client2, $reportSubmission2, $report2);

        foreach ([$reportPdfDoc, $supportingDoc, $reportPdfDoc, $supportingDoc] as $doc) {
            self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $doc->getSynchronisationStatus());
        }
    }

    public function testDocumentLimitsAreRespected(): void
    {
        $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->createAndSubmitReportWithSupportingDoc($this->firstJulyPm);

        $documents = self::$sut
            ->getQueuedDocumentsAndSetToInProgress(2);

        self::assertEquals(2, count($documents));
    }

    public function testDocumentsAreOrderedByIsReportPdf(): void
    {
        [$_, $report, $reportPdfDoc, $_, $_] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);

        foreach (range(1, 5) as $index) {
            $this->createAndSubmitAdditionalDocuments($report, $this->firstJulyPm);
        }

        [$_, $_, $reportPdfDoc2, $_, $_] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);

        $this->createAndSubmitAdditionalDocuments($report, $this->secondJulyPm);

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress(5);

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
}
