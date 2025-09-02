<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use DateInterval;
use App\Entity\Client;
use App\Entity\Report\Checklist;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\User;
use App\Repository\ChecklistRepository;
use App\Tests\Integration\ApiBaseTestCase;

final class ChecklistRepositoryTest extends ApiBaseTestCase
{
    private ChecklistRepository $checklistRepository;
    private DateTime $firstJulyAm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checklistRepository = $this->entityManager->getRepository(Checklist::class);

        $this->purgeDatabase();

        $this->firstJulyAm = DateTime::createFromFormat('d/m/Y', '01/07/2020', new DateTimeZone('UTC'));
    }

    #[Test]
    public function getResubmittableErrorChecklistsAndSetToQueuedTest(): void
    {
        $correctError = 'Foo 500 Internal Server Error Bar';
        $incorrectError = 'some error';
        [$_, $_, $_, $checklistPermanent] = $this->createAndSubmitReportWithChecklist(Checklist::SYNC_STATUS_PERMANENT_ERROR, $correctError);
        [$_, $_, $_, $checklistSuccess] = $this->createAndSubmitReportWithChecklist(Checklist::SYNC_STATUS_SUCCESS, $correctError);
        [$_, $_, $_, $checklistPermanentWrongError] = $this->createAndSubmitReportWithChecklist(Checklist::SYNC_STATUS_PERMANENT_ERROR, $incorrectError);

        $checklists = $this->checklistRepository->getResubmittableErrorChecklistsAndSetToQueued('100');

        $this->entityManager->refresh($checklistPermanent);
        $this->entityManager->refresh($checklistSuccess);
        $this->entityManager->refresh($checklistPermanentWrongError);

        self::assertEquals(count($checklists), 1);
        self::assertEquals(Checklist::SYNC_STATUS_QUEUED, $checklistPermanent->getSynchronisationStatus());
        self::assertEquals(Checklist::SYNC_STATUS_SUCCESS, $checklistSuccess->getSynchronisationStatus());
        self::assertEquals(Checklist::SYNC_STATUS_PERMANENT_ERROR, $checklistPermanentWrongError->getSynchronisationStatus());
    }

    private function createAndSubmitReportWithChecklist(string $status, string $error): array
    {
        // Create Client
        $client = (new Client())->setCaseNumber('abc-123');
        $this->entityManager->persist($client);

        // Create report
        $report = (new Report(
            $client,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            $this->firstJulyAm,
            $this->firstJulyAm->add(new DateInterval('P364D'))
        )
        );
        $this->entityManager->persist($report);

        // Submit Report
        $submittedOn = $this->firstJulyAm;
        $report->setSubmitDate($submittedOn);
        $reportSubmission = (new ReportSubmission($report, $this->generateAndPersistUser()))->setCreatedOn($submittedOn);
        $this->entityManager->persist($reportSubmission);

        // Create Checklist
        $checklist = new Checklist($report);
        $checklist->setSynchronisationStatus($status);
        $checklist->setSynchronisationError($error);
        $this->entityManager->persist($checklist);

        // Flush it all to the DB
        $this->entityManager->flush();

        return [$client, $report, $reportSubmission, $checklist];
    }

    private function generateAndPersistUser(): User
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
}
