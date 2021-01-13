<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use DateTime;

class ReportHelpers
{
    /**
     * @return Report
     */
    public static function createReport(): Report
    {
        $client = ClientHelpers::createClient();
        $startDate = new DateTime('now');
        $endDate = new DateTime('+1 year');

        return (new Report())
            ->setType(Report::TYPE_COMBINED_HIGH_ASSETS)
            ->setSubmittedBy(null)
            ->setSubmitted(false)
            ->setClient($client)
            ->setId(1)
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setDueDate($endDate);
    }

    /**
     * @return Report
     */
    public static function createSubmittedReport(): Report
    {
        $submittedDate = new DateTime();
        $submittedBy = UserHelpers::createUser();
        $documents = [new Document(), new Document()];

        return (self::createReport())
            ->setSubmitDate($submittedDate)
            ->setSubmittedBy($submittedBy)
            ->setSubmitted(true)
            ->setSubmittedDocuments($documents);
    }
}
