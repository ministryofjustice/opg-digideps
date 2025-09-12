<?php

namespace App\Tests\Integration\Entity;

use App\Tests\Integration\ApiTestCase;
use DateTime;
use App\TestHelpers\ReportSubmissionHelper;

/**
 * User Entity test.
 */
class UserTest extends ApiTestCase
{
    public function testGetNumberOfSubmittedReports()
    {
        $this->purgeDatabase();

        $submissionHelper = new ReportSubmissionHelper(self::$entityManager);
        $submittedSubmissions = [];

        foreach (range(1, 2) as $ignored) {
            $submittedSubmissions[] = $submissionHelper->generateAndPersistSubmittedReportSubmission(
                new DateTime()
            );
        }

        // Submit an extra report for first user
        $submissionHelper->submitAndPersistAdditionalSubmissions(
            $submittedSubmissions[0]
        );

        // Create a report submission but dont submit it
        $notSubmittedSubmission = $submissionHelper->generateAndPersistReportSubmission();

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
