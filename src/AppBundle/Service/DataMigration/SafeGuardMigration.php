<?php

namespace AppBundle\Service\DataMigration;

use Doctrine\DBAL\Connection;
use PDO;

class SafeGuardMigration
{
    /**
     * @var Connection
     */
    private $pdo;

    public function __construct(Connection $pdo)
    {
        $this->pdo = $pdo;
    }

    public function migrateAll()
    {
        $ret = [];

        $reports = $this->getReports();

        $stmt = $this->pdo->prepare(
            'UPDATE safeguarding SET how_often_contact_client = :hocc WHERE id=:id');

        foreach ($reports as $report) {
            if (!empty($report['safeg'])) {
                $stmt->execute([
                    'hocc' => $this->how_often_contact_client($report['client']['firstname'], $report['safeg']),
                    'id' => $report['safeg']['id'],
                ]);
            }
        }
    }

    private function how_often_contact_client($client, array $record)
    {
        if ($record['do_you_live_with_client'] != 'no') {
            return;
        }

        $how_often_do_you_visit = $this->translate($record, 'how_often_do_you_visit');
        $how_often_do_you_phone_or_video_call = $this->translate($record, 'how_often_do_you_phone_or_video_call');
        $how_often_do_you_write_email_or_letter = $this->translate($record, 'how_often_do_you_write_email_or_letter');
        $how_often_does_client_see_other_people = $this->translate($record, 'how_often_does_client_see_other_people');
        $anything_else_to_tell = $record['anything_else_to_tell'] ?: '-';

        $template = "I (or other deputies) visit {$client} {$how_often_do_you_visit}\r\n"
            ."I (or other deputies) phone or video call {$client} {$how_often_do_you_phone_or_video_call}\r\n"
            ."I (or other deputies) write emails or letters to {$client} {$how_often_do_you_write_email_or_letter}\r\n"
            ."{$client} sees other people {$how_often_does_client_see_other_people}\r\n"
            ."Anything else: {$anything_else_to_tell}\r\n";

        return $template;
    }

    private function translate($record, $index)
    {
        if (!isset($record[$index])) {
            return 'n.a.';
        }

        $map = [
            'everyday' => 'Every day',
            'once_a_week' => 'At least once a week',
            'once_a_month' => 'At least once a month',
            'more_than_twice_a_year' => 'More than twice a year',
            'once_a_year' => 'Once a year',
            'less_than_once_a_year' => 'Less than once a year',
        ];

        if (!isset($map[$record[$index]])) {
            return 'n.a.';
        }

        return $map[$record[$index]];
    }

    public function getReports()
    {
        $reports = $this->fetchAll('SELECT * from report');

        foreach ($reports as $k => $report) {
            // add safeg
            $reports[$k]['safeg'] = $this->pdo->query('SELECT * from safeguarding WHERE report_id = '.$k)->fetch();
            $reports[$k]['client'] = $this->pdo->query('SELECT * from client WHERE id = '.$report['client_id'])->fetch();
        }

        return $reports;
    }

    /**
     * Return query ASSOC results, using $key as ID.
     *
     * @param string $query
     * @param string $key
     *
     * @return array
     */
    private function fetchAll($query, $key = 'id')
    {
        $results = $this->pdo->query($query)->fetchAll();

        $ret = [];
        foreach ($results as $result) {
            $keyValue = $result[$key];
            $ret[$keyValue] = $result;
        }

        return $ret;
    }
}
