<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use AppBundle\Service\FeatureFlagService;
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

        $this->injectProphecyService(FeatureFlagService::class, function ($service) {
            $service->get(FeatureFlagService::FLAG_DOCUMENT_SYNC)->shouldBeCalled()->willReturn('1');
        });

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

    /**
     * @dataProvider docSynchronisationStatusProvider
     */
    public function testIndexActionShowsSynchronisationStatus($status, $error, $expectation): void
    {
        $document = $this
            ->generateDocument('ready-doc.pdf', $status)
            ->setSynchronisationError($error);

        $this->restClient
            ->arrayToEntities(ReportSubmission::class . '[]', ['placeholder'])
            ->shouldBeCalled()
            ->willReturn([
                $this
                    ->generateReportSubmission('72549273', 'Reynaldo', 'Noud')
                    ->setDocuments([ $document ]),
            ]);

        $crawler = $this->client->request('GET', '/admin/documents/list?status=pending');

        $documentsRow = $crawler->filter('.behat-region-report-submission-documents-1')->first();

        self::assertStringContainsString($expectation, $documentsRow->text());

        if ($error) {
            self::assertStringContainsString('Error: ' . $error, $documentsRow->text());
        } else {
            self::assertStringNotContainsString('Error', $documentsRow->text());
        }
    }

    public function docSynchronisationStatusProvider(): array
    {
        return [
            [Document::SYNC_STATUS_QUEUED, null, 'Queued'],
            [Document::SYNC_STATUS_IN_PROGRESS, null, 'In progress'],
            [Document::SYNC_STATUS_SUCCESS, null, 'Success'],
            [Document::SYNC_STATUS_TEMPORARY_ERROR, 'S3 is unavailable', 'Temporary fail'],
            [Document::SYNC_STATUS_PERMANENT_ERROR, 'Invalid file type application/json', 'Permanent fail'],
        ];
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

    public function testHiddenFromAdmins(): void
    {
        $this->restClient
            ->arrayToEntities(ReportSubmission::class . '[]', ['placeholder'])
            ->shouldBeCalled()
            ->willReturn([
                $this->generateReportSubmission('72549273', 'Dario', 'Lucke')
                    ->setId(47638)
                    ->setDownloadable(true)
            ]);

        $crawler = $this->client->request('GET', '/admin/documents/list');

        $button = $crawler->selectButton('Synchronise');

        self::assertNull($button->getNode(0));
    }

    public function testSynchroniseQueuesDocuments(): void
    {
        $this->mockLoggedInUser(['ROLE_SUPER_ADMIN']);

        $submissionId = 47638;

        $this->restClient
            ->arrayToEntities(ReportSubmission::class . '[]', ['placeholder'])
            ->shouldBeCalled()
            ->willReturn([
                $this->generateReportSubmission('72549273', 'Dario', 'Lucke')
                    ->setId($submissionId)
                    ->setDownloadable(true)
            ]);

        $crawler = $this->client->request('GET', '/admin/documents/list');

        $this->restClient
            ->put("report-submission/$submissionId/queue-documents", [])
            ->shouldBeCalled()
            ->willReturn();

        $form = $crawler->selectButton('Synchronise')->form();
        $form["checkboxes[$submissionId]"]->tick();

        $this->client->submit($form);
    }

    public function testSynchroniseButtonHoldIfFlagOff(): void
    {
        $this->mockLoggedInUser(['ROLE_SUPER_ADMIN']);

        $submissionId = 47638;

        $this->restClient
            ->arrayToEntities(ReportSubmission::class . '[]', ['placeholder'])
            ->shouldBeCalled()
            ->willReturn([
                $this->generateReportSubmission('72549273', 'Dario', 'Lucke')
                    ->setId($submissionId)
                    ->setDownloadable(true)
            ]);

        $crawler = $this->client->request('GET', '/admin/documents/list');

        $button = $crawler->selectButton('Synchronise');
        self::assertNotNull($button->getNode(0));

        $this->injectProphecyService(FeatureFlagService::class, function ($service) {
            $service->get(FeatureFlagService::FLAG_DOCUMENT_SYNC)->shouldBeCalled()->willReturn('0');
        });

        $crawler = $this->client->request('GET', '/admin/documents/list');

        $button = $crawler->selectButton('Synchronise');
        self::assertNull($button->getNode(0));
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
