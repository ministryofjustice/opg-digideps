<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Entity\Repository;

use OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use OPG\Digideps\Backend\Entity\Report\Checklist;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\ChecklistRepository;

class ChecklistRepositoryIntegrationTest extends ApiIntegrationTestCase
{
    private static ChecklistRepository $sut;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ChecklistRepository $repo */
        $repo = self::$entityManager->getRepository(Checklist::class);
        self::$sut = $repo;
    }

    private function createAndSubmitReportWithChecklist($status, $error): Checklist
    {
        $firstJulyAm = \DateTime::createFromFormat('d/m/Y', '01/07/2020', new \DateTimeZone('UTC')) ?: throw new \LogicException('Bad Fixture');
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());


        // Submit Report
        $submittedOn = $firstJulyAm;
        $report->setSubmitDate($submittedOn);
        $reportSubmission = new ReportSubmission($report, $this->generateAndPersistUser())->setCreatedOn($submittedOn);
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
        $datePostFix = (string) new \DateTime()->getTimestamp();
        $user = new User(
            'Test',
            'User',
            sprintf('test-user%s%s@test.com', $datePostFix, rand(0, 100000))
        )
            ->setPassword('password123');

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

        $checklists = self::$sut->getResubmittableErrorChecklistsAndSetToQueued('100');

        self::$entityManager->refresh($checklistPermanent);
        self::$entityManager->refresh($checklistSuccess);
        self::$entityManager->refresh($checklistPermanentWrongError);

        self::assertCount(1, $checklists);
        self::assertEquals(Checklist::SYNC_STATUS_QUEUED, $checklistPermanent->getSynchronisationStatus());
        self::assertEquals(Checklist::SYNC_STATUS_SUCCESS, $checklistSuccess->getSynchronisationStatus());
        self::assertEquals(Checklist::SYNC_STATUS_PERMANENT_ERROR, $checklistPermanentWrongError->getSynchronisationStatus());
    }
}
