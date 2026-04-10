<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\TestHelpers;

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Checklist;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\User;
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
