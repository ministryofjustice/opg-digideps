<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Client\GovUK\BankHolidaysAPIClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCSVUploadedCommand extends DaemonableCommand
{
    public static $defaultName = 'digideps:check-csv-uploaded';

    private BankHolidaysAPIClient $bankHolidayAPIClient;


    public function __construct(BankHolidaysAPIClient $bankHolidayAPIClient) {
        $this->bankHolidayAPIClient = $bankHolidayAPIClient;
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Checks for any occurrences of CSVUploadedEvent and creates AWS CloudWatch Events for missed days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateTime = new \DateTime();

        $currentDate = $dateTime->format('Y-m-d');

        $dates = json_decode($this->bankHolidayAPIClient->getBankHolidays()->getBody(), true);

        $isBankHoliday = array_search($currentDate, array_column($dates['england-and-wales']['events'], 'date'));

        // IF WORKING DAY
        if ($isBankHoliday == false)
        {
            // CHECK CSV UPLOADED EVENT

            // IF NOT CSV UPLOADED
            {
                // CREATE CLOUDWATCH EVENT
            }
        }
    }

}
