<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\TestHelpers;

use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Entity\Report\Report;

class ReportHelpers
{
    public static function createReport(): Report
    {
        $client = ClientHelpers::createClient();
        $startDate = new \DateTime('now');
        $endDate = new \DateTime('+1 year');

        $report = new Report();
        $report->setType(Report::TYPE_COMBINED_HIGH_ASSETS);
        $report->setSubmittedBy(null);
        $report->setSubmitted(false);
        $report->setClient($client);
        $report->setId(1);
        $report->setStartDate($startDate);
        $report->setEndDate($endDate);
        $report->setDueDate($endDate);

        return $report;
    }

    public static function createSubmittedReport(): Report
    {
        $submittedDate = new \DateTime();
        $submittedBy = UserHelpers::createUser();
        $documents = [new Document(), new Document()];

        return (self::createReport())
            ->setSubmitDate($submittedDate)
            ->setSubmittedBy($submittedBy)
            ->setSubmitted(true)
            ->setSubmittedDocuments($documents);
    }
}
