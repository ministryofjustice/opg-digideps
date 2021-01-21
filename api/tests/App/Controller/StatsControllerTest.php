<?php

namespace Tests\App\Controller;

use App\Controller\StatsController;
use App\Entity\Report\Report;
use App\TestHelpers\ReportSubmissionHelper;
use DateTime;
use Doctrine\ORM\EntityManager;

class StatsControllerTest extends AbstractTestController
{
    private EntityManager $entityManager;
    private ReportSubmissionHelper $submissionHelper;

    public function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->submissionHelper = new ReportSubmissionHelper();
    }

    /** @test */
    public function activeLayDeputies()
    {
        $submittedSubmissions = [];
        $unsubmttedSubmissions = [];

        foreach (range(1, 2) as $index) {
            $submittedSubmissions[] = $this->submissionHelper->generateAndPersistSubmittedReportSubmission(
                $this->entityManager,
                new DateTime()
            );
        }

        $this->submissionHelper->submitAndPersistAdditionalSubmissions(
            $this->entityManager,
            $submittedSubmissions[0]
        );

        $unsubmttedSubmissions[] = $this->submissionHelper->generateAndPersistReportSubmission($this->entityManager);

        $response = $this->assertJsonRequest(
            'GET',
            '/stats/activeLays',
            [
                'mustSucceed' => true,
                'AuthToken' => $this->loginAsSuperAdmin(),
                'data' => []
            ]
        );

        $isSubmittedClosure = function (Report $report) {
            return !is_null($report->getSubmitDate());
        };

        $expectedReportsSubmittedFirst = array_filter(
            $submittedSubmissions[0]->getReport()->getClient()->getReports()->toArray(),
            $isSubmittedClosure
        );
        $expectedReportsSubmittedSecond = array_filter(
            $submittedSubmissions[1]->getReport()->getClient()->getReports()->toArray(),
            $isSubmittedClosure
        );

        $expectedResponse = json_encode([
            [
                'userId' => $submittedSubmissions[0]->getReport()->getClient()->getUsers()[0]->getId(),
                'userFullName' => $submittedSubmissions[0]->getReport()->getClient()->getUsers()[0]->getFullName(),
                'userEmail' => $submittedSubmissions[0]->getReport()->getClient()->getUsers()[0]->getEmail(),
                'userPhoneNumber' => $submittedSubmissions[0]->getReport()->getClient()->getUsers()[0]->getPhoneMain(),
                'reportsSubmitted' => count($expectedReportsSubmittedFirst),
                'userRegisteredOn' => $submittedSubmissions[0]->getReport()->getClient()->getUsers()[0]->getRegistrationDate()->format('Y-m-d')
            ],
            [
                'userId' => $submittedSubmissions[1]->getReport()->getClient()->getUsers()[0]->getId(),
                'userFullName' => $submittedSubmissions[1]->getReport()->getClient()->getUsers()[0]->getFullName(),
                'userEmail' => $submittedSubmissions[1]->getReport()->getClient()->getUsers()[0]->getEmail(),
                'userPhoneNumber' => $submittedSubmissions[1]->getReport()->getClient()->getUsers()[0]->getPhoneMain(),
                'reportsSubmitted' => count($expectedReportsSubmittedSecond),
                'userRegisteredOn' => $submittedSubmissions[1]->getReport()->getClient()->getUsers()[0]->getRegistrationDate()->format('Y-m-d')
            ],
        ]);

        self::assertEquals($expectedResponse, $response['data']);
    }
}
