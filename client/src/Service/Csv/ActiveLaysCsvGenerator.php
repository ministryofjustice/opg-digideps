<?php

declare(strict_types=1);

namespace App\Service\Csv;

class ActiveLaysCsvGenerator
{
    public function __construct(private CsvBuilder $csvBuilder)
    {
    }

    /**
     * @return string
     */
    public function generateActiveLaysCsv(array $lays)
    {
        $headers = [
            'Id',
            'Deputy Full Name',
            'Deputy Email',
            'Deputy Phone Number',
            'Reports Submitted',
            'Registered On',
            'Client Full Name',
        ];

        $rows = [];

        foreach ($lays as $lay) {
            $rows[] = [
                $lay['id'],
                sprintf('%s %s', $lay['user_first_name'], $lay['user_last_name']),
                $lay['user_email'],
                $lay['user_phone_number'],
                $lay['submitted_reports'],
                $lay['registration_date'],
                sprintf('%s %s', $lay['client_first_name'], $lay['client_last_name']),
            ];
        }

        return $this->csvBuilder->buildCsv($headers, $rows);
    }
}
