<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use Prophecy\Argument;

class AdminReportSubmissionControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->restClient
            ->get(Argument::containingString('report-submission'), 'array')
            ->willReturn([
                'counts' => [
                    'new' => 2,
                    'pending' => 15,
                    'archived' => 3827,
                ],
                'records' => ['placeholder']
            ]);

        $this->mockLoggedInUser(['ROLE_ADMIN']);
    }

    public function testIndexActionShowsInfo(): void
    {
        $this->restClient
            ->arrayToEntities(ReportSubmission::class . '[]', ['placeholder'])
            ->shouldBeCalled()
            ->willReturn([
                $this
                    ->generateReportSubmission('48745952', 'Jonas', 'Ishibashi')
                    ->setDocuments([
                        $this->generateDocument('report.pdf'),
                        $this->generateDocument('bill-scanned.jpg'),
                    ]),
            ]);

        $crawler = $this->client->request('GET', '/admin/documents/list');

        self::assertStringContainsString(2, $crawler->filter('.behat-link-tab-new + .govuk-tag')->text());
        self::assertStringContainsString(15, $crawler->filter('.behat-link-tab-pending + .govuk-tag')->text());

        $submissionRow = $crawler->filter('.behat-region-report-submission-1')->first();
        self::assertStringContainsString('48745952', $submissionRow->text());
        self::assertStringContainsString('Jonas Ishibashi', $submissionRow->text());

        $documentsRow = $crawler->filter('.behat-region-report-submission-documents-1')->first();
        self::assertStringContainsString('report.pdf', $documentsRow->text());
        self::assertStringContainsString('bill-scanned.jpg', $documentsRow->text());
    }

    public function testIndexActionShowsSynchronisationStatus(): void
    {
        $this->restClient
            ->arrayToEntities(ReportSubmission::class . '[]', ['placeholder'])
            ->shouldBeCalled()
            ->willReturn([
                $this
                    ->generateReportSubmission('72549273', 'Reynaldo', 'Noud')
                    ->setDocuments([
                        $this->generateDocument('ready-doc.pdf', Document::SYNC_STATUS_QUEUED),
                        $this->generateDocument('in-progress-doc.pdf', Document::SYNC_STATUS_IN_PROGRESS),
                        $this->generateDocument('complete-doc.pdf', Document::SYNC_STATUS_SUCCESS),
                        $this->generateDocument('temp-error-doc.pdf', Document::SYNC_STATUS_TEMPORARY_ERROR)
                            ->setSynchronisationError('S3 is unavailable'),
                        $this->generateDocument('permanent-error-doc.pdf', Document::SYNC_STATUS_PERMANENT_ERROR)
                            ->setSynchronisationError('Invalid file type application/json'),
                    ]),
            ]);

        $crawler = $this->client->request('GET', '/admin/documents/list?status=pending');

        $documentRows = $crawler->filter('.behat-region-report-submission-documents-1 table > tbody > tr');

        self::assertStringContainsString('Queued', $documentRows->eq(0)->text());
        self::assertStringContainsString('In progress', $documentRows->eq(1)->text());
        self::assertStringContainsString('Success', $documentRows->eq(2)->text());
        self::assertStringContainsString('Temporary fail', $documentRows->eq(3)->text());
        self::assertStringContainsString('Error: S3 is unavailable', $documentRows->eq(3)->text());
        self::assertStringContainsString('Permanent fail', $documentRows->eq(4)->text());
        self::assertStringContainsString('Error: Invalid file type application/json', $documentRows->eq(4)->text());
    }

    public function testIndexActionShowsWhoArchivedBy(): void
    {
        $user = (new User())
            ->setFirstname('Solomon')
            ->setLastname('Pinedo');

        $this->restClient
            ->arrayToEntities(ReportSubmission::class . '[]', ['placeholder'])
            ->shouldBeCalled()
            ->willReturn([
                $this
                    ->generateReportSubmission('72549273', 'Reynaldo', 'Noud')
                    ->setArchivedBy($user)
            ]);

        $crawler = $this->client->request('GET', '/admin/documents/list?status=archived');

        $archivedBy = $crawler->filter('.behat-region-report-submission-1 td:last-child > span');

        self::assertEquals('SP', $archivedBy->text());
        self::assertEquals('Solomon Pinedo', $archivedBy->attr('title'));
    }

    private function generateReportSubmission(
        string $caseNumber,
        string $firstname,
        string $lastname,
        string $type = Report::TYPE_102
    ): ReportSubmission
    {
        $client = (new Client())
            ->setCaseNumber($caseNumber)
            ->setFirstname($firstname)
            ->setLastname($lastname);

        $report = (new Report())
            ->setType($type)
            ->setClient($client);

        return (new ReportSubmission())
            ->setReport($report);
    }

    private function generateDocument(string $fileName, string $syncStatus = null)
    {
        $document = (new Document())
            ->setFileName($fileName);

        if (!is_null($syncStatus)) {
            $document->setSynchronisationStatus($syncStatus);
        }

        return $document;
    }
}
