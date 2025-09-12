<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Tests\Integration\ApiTestCase;
use DateTime;
use DateTimeZone;
use DateInterval;
use App\Entity\Client;
use App\Entity\Report\Checklist;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\User;
use App\Repository\ChecklistRepository;

class ChecklistRepositoryTest extends ApiTestCase
{
    private ChecklistRepository $checklistRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ChecklistRepository $repo */
        $repo = self::$entityManager->getRepository(Checklist::class);
        $this->checklistRepository = $repo;
    }

    private function createAndSubmitReportWithChecklist($status, $error): Checklist
    {
        $firstJulyAm = DateTime::createFromFormat('d/m/Y', '01/07/2020', new DateTimeZone('UTC'));

        // Create Client
        $client = (new Client())->setCaseNumber('abc-123');
        self::$entityManager->persist($client);

        // Create report
        $report = (
            new Report(
                $client,
                Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
                $firstJulyAm,
                $firstJulyAm->add(new DateInterval('P364D'))
            )
        );

        self::$entityManager->persist($report);

        // Submit Report
        $submittedOn = $firstJulyAm;
        $report->setSubmitDate($submittedOn);
        $reportSubmission = (new ReportSubmission($report, $this->generateAndPersistUser()))->setCreatedOn($submittedOn);
        self::$entityManager->persist($reportSubmission);

        // Create Checklist
        $checklist = new Checklist($report);
        $checklist->setSynchronisationStatus($status);
        $checklist->setSynchronisationError($error);
        self::$entityManager->persist($checklist);

        // Flush it all to the DB
        self::$entityManager->flush();

        return $checklist;
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

    public function testGetResubmittableErrorChecklistsAndSetToQueuedTest(): void
    {
        $correctError = 'Foo 500 Internal Server Error Bar';
        $incorrectError = 'some error';
        $checklistPermanent = $this->createAndSubmitReportWithChecklist(Checklist::SYNC_STATUS_PERMANENT_ERROR, $correctError);
        $checklistSuccess = $this->createAndSubmitReportWithChecklist(Checklist::SYNC_STATUS_SUCCESS, $correctError);
        $checklistPermanentWrongError = $this->createAndSubmitReportWithChecklist(Checklist::SYNC_STATUS_PERMANENT_ERROR, $incorrectError);

        $checklists = $this->checklistRepository->getResubmittableErrorChecklistsAndSetToQueued('100');

        self::$entityManager->refresh($checklistPermanent);
        self::$entityManager->refresh($checklistSuccess);
        self::$entityManager->refresh($checklistPermanentWrongError);

        self::assertEquals(count($checklists), 1);
        self::assertEquals(Checklist::SYNC_STATUS_QUEUED, $checklistPermanent->getSynchronisationStatus());
        self::assertEquals(Checklist::SYNC_STATUS_SUCCESS, $checklistSuccess->getSynchronisationStatus());
        self::assertEquals(Checklist::SYNC_STATUS_PERMANENT_ERROR, $checklistPermanentWrongError->getSynchronisationStatus());
    }
}
