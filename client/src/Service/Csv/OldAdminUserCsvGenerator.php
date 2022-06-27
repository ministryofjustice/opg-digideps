<?php

namespace App\Service\Csv;

class OldAdminUserCsvGenerator
{
    private CsvBuilder $csvBuilder;

    public function __construct(CsvBuilder $csvBuilder)
    {
        $this->csvBuilder = $csvBuilder;
    }

    /**
     * @return string
     */
    public function generateOldAdminUsersCsv(array $adminUsers)
    {
        $headers = [
            'Id',
            'Full Name',
            'Email Address',
            'Last Logged In Date',
            'Account Activated',
            'Role Name',
        ];

        $rows = [];

        foreach ($adminUsers as $users) {
            foreach ($users as $user) {
                $rows[] = [
                    $user['id'],
                    sprintf('%s %s', $user['firstname'], $user['lastname']),
                    $user['email'],
                    $user['last_logged_in'],
                    $user['active'] ? 'Yes' : 'No',
                    $user['role_name']
                ];
            }
        }
        return $this->csvBuilder->buildCsv($headers, $rows);
    }
}
