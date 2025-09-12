<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Tests\Integration\ApiTestTrait;
use DateTime;
use DateInterval;
use DateTimeZone;
use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\ReportInterface;
use App\Entity\User;
use App\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DocumentRepositoryTest extends KernelTestCase
{
    use ApiTestTrait;

    private DateTime $firstJulyAm;
    private DateTime $firstJulyPm;
    private DateTime $secondJulyAm;
    private DateTime $secondJulyPm;
    private DateTime $thirdJulyAm;
    private DateTime $thirdJulyPm;

    private static DocumentRepository $sut;

    protected function setUp(): void
    {
        parent::setUp();

        self::configureTest();

        self::purgeDatabase();

        /** @var DocumentRepository $repo */
        $repo = self::$entityManager->getRepository(Document::class);
        self::$sut = $repo;

        $this->firstJulyAm = DateTime::createFromFormat('d/m/Y', '01/07/2020', new DateTimeZone('UTC'));
        $this->firstJulyPm = clone $this->firstJulyAm->add(new DateInterval('PT20H'));
        $this->secondJulyAm = DateTime::createFromFormat('d/m/Y', '02/07/2020', new DateTimeZone('UTC'));
        $this->secondJulyPm = clone $this->secondJulyAm->add(new DateInterval('PT20H'));
        $this->thirdJulyAm = DateTime::createFromFormat('d/m/Y', '03/07/2020', new DateTimeZone('UTC'));
        $this->thirdJulyPm = clone $this->thirdJulyAm->add(new DateInterval('PT20H'));
    }

    private function createAndSubmitReportWithSupportingDoc(DateTime $submittedOn): array
    {
        $client = $this->generateAndPersistClient('abc-123');
        $report = $this->generateAndPersistReport($client, false);
        $reportPdfDoc = $this->generateAndPersistDocument($report, true, 'QUEUED', $this->firstJulyPm, false);
        $supportingDoc = $this->generateAndPersistDocument($report, false, 'QUEUED', $this->firstJulyAm, false);

        $reportSubmission = $this->submitReport($report, $submittedOn, $reportPdfDoc, $supportingDoc);

        self::$entityManager->flush();

        return [$client, $report, $reportPdfDoc, $supportingDoc, $reportSubmission];
    }

    private function generateAndPersistClient(string $caseNumber): Client
    {
        $client = (new Client())->setCaseNumber($caseNumber);

        self::$entityManager->persist($client);

        return $client;
    }

    private function generateAndPersistReport(Client $client, bool $isNdr): Report|Ndr
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

        self::$entityManager->persist($report);

        return $report;
    }

    private function generateAndPersistDocument(ReportInterface $report, bool $isReportPdf, string $syncStatus, DateTime $createdOn, bool $isResubmission): Document
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

        self::$entityManager->persist($doc);

        return $doc;
    }

    private function submitReport(ReportInterface $report, DateTime $submittedOn, Document $reportPdf, ?Document $supportingDocument): ReportSubmission
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

    private function generateAndPersistReportSubmission(ReportInterface $report, DateTime $createdOn): ReportSubmission
    {
        $submission = (new ReportSubmission($report, $this->generateAndPersistUser()))->setCreatedOn($createdOn);

        self::$entityManager->persist($submission);

        return $submission;
    }

    private function generateAndPersistUser(): User
    {
        $user = (new User())
            ->setFirstname('Test')
            ->setLastname('User')
            ->setPassword('password123');

        $datePostFix = (string) (new DateTime())->getTimestamp();
        $user->setEmail(sprintf('test-user%s%s@test.com', $datePostFix, rand(0, 100000)));

        self::$entityManager->persist($user);

        return $user;
    }

    private function assertDataMatchesEntity(
        array $documents,
        Document $document,
        Client $client,
        ReportSubmission $submission,
        Report|Ndr $report,
    ): void {
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

    private function createFailedDocumentSubmission($status, $createdOn, $caseNumber, $archived): void
    {
        $client = $this->generateAndPersistClient('abc-123-'.$caseNumber);
        $report = $this->generateAndPersistReport($client, false);
        $reportPdfDoc = $this->generateAndPersistDocument($report, true, $status, $this->firstJulyAm, false);
        $supportingDoc = $this->generateAndPersistDocument($report, false, $status, $this->firstJulyAm, false);
        $reportSubmission = $this->submitReport($report, $this->firstJulyPm, $reportPdfDoc, $supportingDoc);
        $reportSubmission->setCreatedOn($createdOn);
        $reportSubmission->setArchived($archived);
        self::$entityManager->persist($reportSubmission);
        self::$entityManager->flush();
    }

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

    private function createAndSubmitNdr(): array
    {
        $client = $this->generateAndPersistClient('abc-123');
        $ndr = $this->generateAndPersistReport($client, true);
        $reportPdfDoc = $this->generateAndPersistDocument($ndr, true, 'QUEUED', $this->firstJulyAm, false);

        $reportSubmission = $this->submitReport($ndr, $this->secondJulyPm, $reportPdfDoc, null);

        self::$entityManager->flush();

        return [$client, $ndr, $reportPdfDoc, $reportSubmission];
    }

    private function createAndSubmitAdditionalDocuments(ReportInterface $report, DateTime $submittedOn)
    {
        $additionalSubmission = $this->generateAndPersistReportSubmission($report, $submittedOn);
        $additionalSupportingDoc = $this->generateAndPersistDocument($report, false, 'QUEUED', $submittedOn, false);

        $additionalSubmission->addDocument($additionalSupportingDoc);
        $additionalSupportingDoc->setReportSubmission($additionalSubmission);

        self::$entityManager->persist($additionalSupportingDoc);
        self::$entityManager->persist($additionalSubmission);

        return [$additionalSubmission, $additionalSupportingDoc];
    }

    private function createAndSubmitResubmissionWithSupportingDoc(ReportInterface $report, DateTime $submittedOn): array
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

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress('100');

        self::$entityManager->refresh($reportPdfDoc);
        self::$entityManager->refresh($supportingDoc);

        $this->assertDataMatchesEntity($documents, $reportPdfDoc, $client, $reportSubmission, $report);
        $this->assertDataMatchesEntity($documents, $supportingDoc, $client, $reportSubmission, $report);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $reportPdfDoc->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $supportingDoc->getSynchronisationStatus());
    }

    public function testGetResubmittableErrorDocumentsAndSetToQueued(): void
    {
        [$_, $_, $reportPdfDocValid, $supportingDocValid, $_] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        [$_, $_, $reportPdfDocNotValid, $supportingDocNotValid, $_] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        [$_, $_, $reportPdfDocInProgressNow, $supportingDocInProgressNow, $reportSubmissionNow] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        [$_, $_, $reportPdfDocInProgressOld, $supportingDocInProgressOld, $reportSubmissionOld] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);

        $reportPdfDocValid->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
        $reportPdfDocValid->setSynchronisationError('Document failed to sync after 4 attempts');
        $supportingDocValid->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
        $supportingDocValid->setSynchronisationError('Report PDF failed to sync');

        $reportPdfDocNotValid->setSynchronisationStatus(Document::SYNC_STATUS_SUCCESS);
        $supportingDocNotValid->setSynchronisationStatus(Document::SYNC_STATUS_PERMANENT_ERROR);
        $supportingDocNotValid->setSynchronisationError('Some non resubmittable error message');

        $reportPdfDocInProgressNow->setSynchronisationStatus(Document::SYNC_STATUS_IN_PROGRESS);
        $supportingDocInProgressNow->setSynchronisationStatus(Document::SYNC_STATUS_IN_PROGRESS);
        $currentDateTime = new DateTime('now');
        $reportSubmissionNow->setCreatedOn($currentDateTime);

        $reportPdfDocInProgressOld->setSynchronisationStatus(Document::SYNC_STATUS_IN_PROGRESS);
        $supportingDocInProgressOld->setSynchronisationStatus(Document::SYNC_STATUS_IN_PROGRESS);
        $lastYearDateTime = new DateTime('now -1 year');
        $reportSubmissionOld->setCreatedOn($lastYearDateTime);

        self::$entityManager->persist($reportPdfDocValid);
        self::$entityManager->persist($supportingDocValid);
        self::$entityManager->persist($reportPdfDocNotValid);
        self::$entityManager->persist($supportingDocNotValid);
        self::$entityManager->persist($reportPdfDocInProgressNow);
        self::$entityManager->persist($supportingDocInProgressNow);
        self::$entityManager->persist($reportPdfDocInProgressOld);
        self::$entityManager->persist($supportingDocInProgressOld);
        self::$entityManager->persist($reportSubmissionNow);
        self::$entityManager->persist($reportSubmissionOld);
        self::$entityManager->flush();
        self::assertEquals(Document::SYNC_STATUS_PERMANENT_ERROR, $reportPdfDocValid->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_PERMANENT_ERROR, $supportingDocValid->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_SUCCESS, $reportPdfDocNotValid->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_PERMANENT_ERROR, $supportingDocNotValid->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $supportingDocInProgressNow->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $supportingDocInProgressOld->getSynchronisationStatus());

        $documents = self::$sut->getResubmittableErrorDocumentsAndSetToQueued('100');
        self::$entityManager->refresh($reportPdfDocValid);
        self::$entityManager->refresh($supportingDocValid);
        self::$entityManager->refresh($reportPdfDocNotValid);
        self::$entityManager->refresh($supportingDocNotValid);
        self::$entityManager->refresh($supportingDocInProgressNow);
        self::$entityManager->refresh($supportingDocInProgressOld);

        // 2 permanent error docs and 2 of the in progress docs
        self::assertEquals(4, count($documents));
        self::assertEquals(Document::SYNC_STATUS_QUEUED, $reportPdfDocValid->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_QUEUED, $supportingDocValid->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_SUCCESS, $reportPdfDocNotValid->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_PERMANENT_ERROR, $supportingDocNotValid->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_QUEUED, $supportingDocInProgressOld->getSynchronisationStatus());
        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $supportingDocInProgressNow->getSynchronisationStatus());
    }

    public function testLogFailedDocuments(): void
    {
        $currentDateTime = new DateTime(); // Current date and time
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
        [$_, $_, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->syncDocuments([$reportPdfDoc], $reportSubmission, 'abc-123-abc-123');

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress('100');

        self::assertEquals('abc-123-abc-123', $documents[$supportingDoc->getId()]['report_submission_uuid']);
    }

    public function testGetQueuedDocumentsAndSetToInProgressSupportsNdrs()
    {
        [$client, $ndr, $reportPdfDoc, $reportSubmission] = $this->createAndSubmitNdr();

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress('100');

        $this->assertDataMatchesEntity($documents, $reportPdfDoc, $client, $reportSubmission, $ndr);

        self::$entityManager->refresh($reportPdfDoc);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $reportPdfDoc->getSynchronisationStatus());
    }

    public function testAdditionalDocumentsSubmissionsUseOriginalSubmissionUUID(): void
    {
        [$_, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->syncDocuments([$reportPdfDoc, $supportingDoc], $reportSubmission, null);
        [$additionalSubmission, $additionalSupportingDoc] = $this->createAndSubmitAdditionalDocuments($report, $this->thirdJulyAm);

        self::$entityManager->flush();

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress('100');

        self::$entityManager->refresh($additionalSupportingDoc);

        self::assertEquals(Document::SYNC_STATUS_IN_PROGRESS, $additionalSupportingDoc->getSynchronisationStatus());
        self::assertEquals($reportSubmission->getUuid(), $documents[$additionalSupportingDoc->getId()]['report_submission_uuid']);
        self::assertEquals($additionalSubmission->getId(), $documents[$additionalSupportingDoc->getId()]['report_submission_id']);
    }

    public function testResubmissionsDoNotHaveOriginalSubmissionUUIDOnSubmission(): void
    {
        [$_, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->firstJulyAm);
        $this->syncDocuments([$reportPdfDoc, $supportingDoc], $reportSubmission, 'abc-123-abc-123');
        [$resubmissionReportPdfDoc, $resubmissionSupportingDoc, $reportResubmission] = $this->createAndSubmitResubmissionWithSupportingDoc($report, $this->secondJulyAm);

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress('100');

        self::$entityManager->refresh($resubmissionReportPdfDoc);
        self::$entityManager->refresh($resubmissionSupportingDoc);

        self::assertEquals(null, $documents[$resubmissionReportPdfDoc->getId()]['report_submission_uuid']);
        self::assertEquals($reportResubmission->getId(), $documents[$resubmissionReportPdfDoc->getId()]['report_submission_id']);
        self::assertEquals(null, $documents[$resubmissionSupportingDoc->getId()]['report_submission_uuid']);
        self::assertEquals($reportResubmission->getId(), $documents[$resubmissionSupportingDoc->getId()]['report_submission_id']);
    }

    public function testAdditionalDocsOnResubmissionsUseResubmissionUUID(): void
    {
        [$_, $report, $reportPdfDoc, $supportingDoc, $reportSubmission] = $this->createAndSubmitReportWithSupportingDoc($this->secondJulyAm);
        $this->syncDocuments([$reportPdfDoc, $supportingDoc], $reportSubmission, 'abc-123-abc-123');

        [$resubmissionReportPdfDoc, $resubmissionSupportingDoc, $reportResubmission] = $this->createAndSubmitResubmissionWithSupportingDoc($report, $this->thirdJulyAm);
        $this->syncDocuments([$resubmissionReportPdfDoc, $resubmissionSupportingDoc], $reportResubmission, 'def-456-def-456');

        [$additionalSubmission, $additionalSupportingDoc] = $this->createAndSubmitAdditionalDocuments($report, $this->thirdJulyPm);

        self::$entityManager->flush();

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress('100');

        $this->syncDocuments([$additionalSupportingDoc], $additionalSubmission, 'def-456-def-456');

        self::$entityManager->refresh($additionalSubmission);
        self::$entityManager->refresh($additionalSupportingDoc);

        self::assertEquals($additionalSubmission->getUuid(), $documents[$additionalSupportingDoc->getId()]['report_submission_uuid']);
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
            ->getQueuedDocumentsAndSetToInProgress('100');

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
            ->getQueuedDocumentsAndSetToInProgress('2');

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

        $documents = self::$sut->getQueuedDocumentsAndSetToInProgress('5');

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
