<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Client\GovUK\BankHolidaysAPIClient;
use App\Service\SecretManagerService;
use App\Service\Time\DateTimeProvider;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\ClientFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCSVUploadedCommand extends DaemonableCommand
{
    public static $defaultName = 'digideps:check-csv-uploaded';

    private BankHolidaysAPIClient $bankHolidayAPIClient;

    private DateTimeProvider $dateTimeProvider;

    private SecretManagerService $secretManagerService;

    public function __construct(BankHolidaysAPIClient $bankHolidayAPIClient, DateTimeProvider $dateTimeProvider, SecretManagerService $secretManagerService)
    {
        $this->bankHolidayAPIClient = $bankHolidayAPIClient;
        $this->dateTimeProvider = $dateTimeProvider;
        $this->secretManagerService = $secretManagerService;
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Checks for any occurrences of CSVUploadedEvent and creates AWS CloudWatch Events for missed days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateTime = $this->dateTimeProvider->getDateTime();

        $currentDate = $dateTime->format('Y-m-d');

        $dates = $this->bankHolidayAPIClient->getBankHolidays();

        $isBankHoliday = array_search($currentDate, array_column($dates['england-and-wales']['events'], 'date'));

        $token = $this->secretManagerService->getSecret('opgresponseslacktoken');

        /** @var Client $client */
        $client = ClientFactory::create($token);

        $result = $client->chatPostMessage([
                                               'username' => 'opg_response',
                                               'channel' => 'random',
                                               'text' => 'Hello world',
                                           ]);

        // IF WORKING DAY
        if (false == $isBankHoliday) {
            // CHECK CSV UPLOADED EVENT

            // IF NOT CSV UPLOADED

                // CREATE CLOUDWATCH EVENT
        }

        return 0;
    }
}
