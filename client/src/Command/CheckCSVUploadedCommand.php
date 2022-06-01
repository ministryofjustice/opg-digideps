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
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CheckCSVUploadedCommand extends DaemonableCommand
{
    public const CSV_NOT_UPLOADED_SLACK_MESSAGE = ':cat_spin: The %s CSV has not been uploaded within the past 24 hours';
    public const FAILED_TO_RETRIEVE_BANK_HOLIDAYS_SLACK_MESSAGE = 'Failed to retrieve bank holidays from Gov.uk. Error message: %s';
    public const FAILED_TO_RETRIEVE_AUDIT_LOG_SLACK_MESSAGE = 'Failed to retrieve audit logs during CSV upload check. Error message: %s';
    public const LOG_GROUP_NOT_CREATED_SLACK_MESSAGE = 'A log group with the name "%s" could not be found. Unable to determine if CSVs have been uploaded.';
    public const UNEXPECTED_ERROR_SLACK_MESSAGE = 'An unexpected error occurred during CSV upload check. Error message: %s';

    public const LAY_CSV = 'LAY';
    public const ORG_CSV = 'ORG';

    public static $defaultName = 'digideps:check-csv-uploaded';

    private DateTime $now;

    public function __construct(
        private BankHolidaysAPIClient $bankHolidayAPIClient,
        private DateTimeProvider $dateTimeProvider,
        private SecretManagerService $secretManagerService,
        private ClientFactory $slackClientFactory,
        private AwsAuditLogHandler $awsAuditLogHandler,
        private LoggerInterface $logger,
        private string $auditLogGroupName
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription(
            'Checks for any occurrences of CSVUploadedEvent and creates AWS CloudWatch Events for missed days'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->now = $this->dateTimeProvider->getDateTime();
        $currentDate = $this->now->format('Y-m-d');

        try {
            $dates = $this->bankHolidayAPIClient->getBankHolidays();
        } catch (Throwable $e) {
            $this->postSlackMessage(sprintf(self::FAILED_TO_RETRIEVE_BANK_HOLIDAYS_SLACK_MESSAGE, $e->getMessage()), 'opg-digideps-devs');

            return 1;
        }

        $isBankHoliday = array_search($currentDate, array_column($dates['england-and-wales']['events'], 'date'));

        // Do not alert on Bank Holidays
        if (false === $isBankHoliday) {
            $logStreams = $this->getLogStreams();

            if (empty($logStreams)) {
                $this->alertNoCSVsWereUploaded();

                return 0;
            }

            $logEvents = $this->getLogEvents();

            // Process should exit as either no events at all or an error has occurred
            if (is_int($logEvents)) {
                return $logEvents;
            }

            if (!empty($logEvents)) {
                // Check and alert for CSV types which have not been uploaded
                $messages = array_column($logEvents, 'message');
                $this->checkCsvsHaveBeenUploadedAndAlert($messages);
            }
        }

        return 0;
    }

    private function getLogEvents(): int|array
    {
        // Calculate 24 hour period start time and end time
        $startingTime = (int) (clone $this->now)->sub(new DateInterval('P1D'))->format('Uv');
        $endTime = (int) (clone $this->now)->format('Uv');

        try {
            $logEvents = $this->awsAuditLogHandler->getLogEventsByLogStream(
                'CSV_UPLOADED',
                $startingTime,
                $endTime,
                $this->auditLogGroupName
            )->get('events');
        } catch (AwsException $e) {
            // AWS returns a 400 response if the log stream is empty or log group does not exist with ResourceNotFoundException code
            if ('ResourceNotFoundException' === $e->getAwsErrorCode()) {
                $this->alertNoCSVsWereUploaded();

                return 0;
            } else {
                $this->postSlackMessage(sprintf(self::FAILED_TO_RETRIEVE_AUDIT_LOG_SLACK_MESSAGE, $e->getMessage()), 'opg-digideps-devs');

                return 1;
            }
        }

        return $logEvents;
    }

    private function getLogStreams()
    {
        try {
            return $this->awsAuditLogHandler->getLogStreams($this->auditLogGroupName);
        } catch (AwsException $e) {
            if ('ResourceNotFoundException' === $e->getAwsErrorCode()) {
                $this->postSlackMessage(sprintf(self::LOG_GROUP_NOT_CREATED_SLACK_MESSAGE, $this->auditLogGroupName), 'opg-digideps-devs');
            } else {
                $this->postSlackMessage(sprintf(self::UNEXPECTED_ERROR_SLACK_MESSAGE, $e->getAwsErrorMessage()), 'opg-digideps-devs');
            }
        }
    }

    private function alertNoCSVsWereUploaded()
    {
        foreach ([self::LAY_CSV, self::ORG_CSV] as $csvType) {
            $this->postSlackMessage(sprintf(self::CSV_NOT_UPLOADED_SLACK_MESSAGE, $csvType), 'opg-digideps-team');
        }
    }

    private function checkCsvsHaveBeenUploadedAndAlert(array $events)
    {
        $typesAndRegexes = [
            self::LAY_CSV => '/"role_type":"LAY"/',
            self::ORG_CSV => '/"role_type":"ORG"/',
        ];

        foreach ($typesAndRegexes as $type => $regex) {
            $uploadEvents = preg_grep($regex, $events);

            if (empty($uploadEvents)) {
                $this->postSlackMessage(sprintf(self::CSV_NOT_UPLOADED_SLACK_MESSAGE, $type), 'opg-digideps-team');
            }
        }
    }

    private function postSlackMessage(string $message, string $channel)
    {
        $token = $this->secretManagerService->getSecret(SecretManagerService::SLACK_APP_TOKEN_SECRET_NAME);

        $client = $this->slackClientFactory->createClient($token);

        try {
            $this->logger->log('notice', 'Posting CSV upload check to slack');

            $client->chatPostMessage(
                [
                    'username' => 'opg-alerts',
                    'channel' => $channel,
                    'text' => $message,
                ]
            );
        } catch (Throwable $e) {
            $this->logger->log('error', sprintf('Failed to post to Slack during CSV upload check: %s', $e->getMessage()));
        }
    }
}
