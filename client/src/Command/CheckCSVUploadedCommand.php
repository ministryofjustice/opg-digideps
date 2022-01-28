<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Audit\AwsAuditLogHandler;
use App\Service\Client\GovUK\BankHolidaysAPIClient;
use App\Service\SecretManagerService;
use App\Service\Time\DateTimeProvider;
use DateInterval;
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

    private ClientFactory $slackClientFactory;
    private AwsAuditLogHandler $awsAuditLogHandler;

    public function __construct(
        BankHolidaysAPIClient $bankHolidayAPIClient,
        DateTimeProvider $dateTimeProvider,
        SecretManagerService $secretManagerService,
        ClientFactory $slackClientFactory,
        AwsAuditLogHandler $awsAuditLogHandler
    ) {
        $this->bankHolidayAPIClient = $bankHolidayAPIClient;
        $this->dateTimeProvider = $dateTimeProvider;
        $this->secretManagerService = $secretManagerService;
        $this->slackClientFactory = $slackClientFactory;
        $this->awsAuditLogHandler = $awsAuditLogHandler;
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Checks for any occurrences of CSVUploadedEvent and creates AWS CloudWatch Events for missed days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = $this->dateTimeProvider->getDateTime();

        $currentDate = $now->format('Y-m-d');

        $dates = $this->bankHolidayAPIClient->getBankHolidays();

        $isBankHoliday = array_search($currentDate, array_column($dates['england-and-wales']['events'], 'date'));

        // IF WORKING DAY
        if (false == $isBankHoliday) {
            // CHECK CSV UPLOADED EVENT
            $startingTime = (int) (clone $now)->sub(new DateInterval('P1D'))->format('Uv');
            $endTime = (int) (clone $now)->format('Uv');

            $test = '';
            $logEvents = $this->awsAuditLogHandler->getLogEventsByLogStream(
                'CSV_UPLOADED',
                $startingTime,
                $endTime
            );

            // IF NOT CSV UPLOADED
            if (empty($logEvents)) {
                $token = $this->secretManagerService->getSecret(SecretManagerService::SLACK_APP_TOKEN_SECRET_NAME);

                // POST TO SLACK
                /** @var Client $client */
                $client = ClientFactory::create($token);

                $result = $client->chatPostMessage([
                    'username' => 'opg_response',
                    'channel' => 'random',
                    'text' => 'Hello world',
                ]);
            }
        }

        return 0;
    }
}
