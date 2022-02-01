<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Audit\AwsAuditLogHandler;
use App\Service\Client\GovUK\BankHolidaysAPIClient;
use App\Service\Client\Slack\ClientFactory;
use App\Service\SecretManagerService;
use App\Service\Time\DateTimeProvider;
use Aws\Exception\AwsException;
use DateInterval;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

class CheckCSVUploadedCommand extends DaemonableCommand
{
    public const CSV_NOT_UPLOADED_SLACK_MESSAGE = 'The %s CSV has not been uploaded within the past 24 hours';

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

            try {
                $logEvents = $this->awsAuditLogHandler->getLogEventsByLogStream(
                    'CSV_UPLOADED',
                    $startingTime,
                    $endTime
                );
            } catch (AwsException $e) {
                // AWS returns a 400 response if the log stream is empty
                if (Response::HTTP_BAD_REQUEST === $e->getAwsErrorCode()) {
                    $logEvents = [];
                } else {
                    throw new RuntimeException($e->getMessage());
                }
            }

            // IF NOT CSV UPLOADED
            if (empty($logEvents)) {
                $token = $this->secretManagerService->getSecret(SecretManagerService::SLACK_APP_TOKEN_SECRET_NAME);

                // POST TO SLACK - need to wrap in helper class as create is a static class function
                $client = $this->slackClientFactory->createClient($token);

                $client->chatPostMessage([
                    'username' => 'opg_response',
                    'channel' => 'opg-digideps-team',
                    'text' => self::CSV_NOT_UPLOADED_SLACK_MESSAGE,
                ]);
            }
        }

        return 0;
    }
}
