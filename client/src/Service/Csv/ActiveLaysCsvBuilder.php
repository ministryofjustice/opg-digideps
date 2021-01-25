<?php declare(strict_types=1);


namespace App\Service\Csv;

use App\Entity\User;

class ActiveLaysCsvBuilder
{
    /**
     * @var CsvBuilder
     */
    private CsvBuilder $csvBuilder;

    public function __construct(CsvBuilder $csvBuilder)
    {
        $this->csvBuilder = $csvBuilder;
    }

    /**
     * @param User[] $lays
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
            'Client Full Name'
        ];

        $rows = [];

        foreach ($lays as $lay) {
            $rows[] = [
                $lay->getId(),
                $lay->getFullName(),
                $lay->getEmail(),
                $lay->getPhoneMain(),
                $lay->getNumberOfSubmittedReports(),
                $lay->getRegistrationDate()->format('j F Y'),
                $lay->getFirstClient()->getFullName()
            ];
        }

        return $this->csvBuilder->buildCsv($headers, $rows);
    }
}
