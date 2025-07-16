<?php

namespace App\Tests\Integration\Entity;

use App\TestHelpers\ReportSubmissionHelper;
use App\Tests\Integration\ApiBaseTestCase;

/**
 * User Entity test.
 */
class UserTest extends ApiBaseTestCase
{
    public function testGetNumberOfSubmittedReports()
    {
        $this->purgeDatabase();

        $submissionHelper = new ReportSubmissionHelper();
        $submittedSubmissions = [];

        foreach (range(1, 2) as $ignored) {
            $submittedSubmissions[] = $submissionHelper->generateAndPersistSubmittedReportSubmission(
                $this->entityManager,
                new \DateTime()
            );
        }

        // Submit an extra report for first user
        $submissionHelper->submitAndPersistAdditionalSubmissions(
            $this->entityManager,
            $submittedSubmissions[0]
        );

        // Create a report submission but dont submit it
        $notSubmittedSubmission = $submissionHelper->generateAndPersistReportSubmission($this->entityManager);

        self::assertEquals(
            2,
            $submittedSubmissions[0]->getReport()->getClient()->getUsers()[0]->getNumberOfSubmittedReports()
        );

        self::assertEquals(
            1,
            $submittedSubmissions[1]->getReport()->getClient()->getUsers()[0]->getNumberOfSubmittedReports()
        );

        self::assertEquals(
            0,
            $notSubmittedSubmission->getReport()->getClient()->getUsers()[0]->getNumberOfSubmittedReports()
        );
    }
}
