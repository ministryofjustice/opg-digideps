<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Report\Checklist;
use App\Entity\Report\Report;
use App\Entity\User;
use DateTime;

class ChecklistTestHelper
{
    public static function buildPfaHighReport(int $id, string $email, string $caseNumber): Report
    {
        $user = (new User())->setEmail($email);

        $report = (new Report())
            ->setStartDate(new DateTime('2020-02-01'))
            ->setEndDate(new DateTime('2021-02-01'))
            ->setReportSubmissions([])
            ->setType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS);

        $checklist = (new Checklist($report))->setSubmittedBy($user);
        $checklist->setId($id);

        $report->setChecklist($checklist);

        $client = new Client();
        $client->setCaseNumber($caseNumber);

        $report->setClient($client);

        return $report;
    }
}
