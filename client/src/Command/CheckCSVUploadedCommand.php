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
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CheckCSVUploadedCommand extends DaemonableCommand
{
    public const CSV_NOT_UPLOADED_SLACK_MESSAGE = 'The %s CSV has not been uploaded within the past 24 hours :cat_spin:';
    public const FAILED_TO_RECEIVE_AUDIT_LOG_SLACK_MESSAGE = 'Failed to retrieve audit logs during CSV upload check. Error message: %s';

    public const CASREC_LAY_CSV = 'CasRec Lay';
    public const SIRIUS_LAY_CSV = 'Sirius Lay';
    public const CASREC_PROF_CSV = 'CasRec Prof';
    public const CASREC_PA_CSV = 'CasRec PA';

    public static $defaultName = 'digideps:check-csv-uploaded';

    private BankHolidaysAPIClient $bankHolidayAPIClient;

    private DateTimeProvider $dateTimeProvider;

    private SecretManagerService $secretManagerService;

    private ClientFactory $slackClientFactory;

    private AwsAuditLogHandler $awsAuditLogHandler;

    private LoggerInterface $logger;

    public function __construct(
        BankHolidaysAPIClient $bankHolidayAPIClient,
        DateTimeProvider $dateTimeProvider,
        SecretManagerService $secretManagerService,
        ClientFactory $slackClientFactory,
        AwsAuditLogHandler $awsAuditLogHandler,
        LoggerInterface $logger
    ) {
        $this->bankHolidayAPIClient = $bankHolidayAPIClient;
        $this->dateTimeProvider = $dateTimeProvider;
        $this->secretManagerService = $secretManagerService;
        $this->slackClientFactory = $slackClientFactory;
        $this->awsAuditLogHandler = $awsAuditLogHandler;
        $this->logger = $logger;
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
        $now = $this->dateTimeProvider->getDateTime();
        $currentDate = $now->format('Y-m-d');

        $dates = $this->bankHolidayAPIClient->getBankHolidays();

        $isBankHoliday = array_search($currentDate, array_column($dates['england-and-wales']['events'], 'date'));

        // Do not alert on Bank Holidays
        if (false == $isBankHoliday) {
            // Calculate 24 hour period start time and end time
            $startingTime = (int) (clone $now)->sub(new DateInterval('P1D'))->format('Uv');
            $endTime = (int) (clone $now)->format('Uv');

            $logEvents = [];
            try {
                $logEvents = $this->awsAuditLogHandler->getLogEventsByLogStream(
                    'CSV_UPLOADED',
                    $startingTime,
                    $endTime
                )->get('events');
            } catch (AwsException $e) {
                // AWS returns a 400 response if the log stream is empty
                if (Response::HTTP_BAD_REQUEST === $e->getAwsErrorCode()) {
                    // Alert on Slack for all CSV types
                    $this->postSlackMessage(sprintf(self::CSV_NOT_UPLOADED_SLACK_MESSAGE, self::CASREC_LAY_CSV));
                    $this->postSlackMessage(sprintf(self::CSV_NOT_UPLOADED_SLACK_MESSAGE, self::SIRIUS_LAY_CSV));
                    $this->postSlackMessage(sprintf(self::CSV_NOT_UPLOADED_SLACK_MESSAGE, self::CASREC_PROF_CSV));
                    $this->postSlackMessage(sprintf(self::CSV_NOT_UPLOADED_SLACK_MESSAGE, self::CASREC_PA_CSV));

                    return 0;
                } else {
                    $this->postSlackMessage(sprintf(self::FAILED_TO_RECEIVE_AUDIT_LOG_SLACK_MESSAGE, $e->getMessage()));

                    return 1;
                }
            }

            if (!empty($logEvents)) {
                // Check and alert for CSV types which have not been uploaded
                $messages = array_column($logEvents, 'message');
                $this->checkCasRecLayCSVHasBeenUploaded($messages);
                $this->checkSiriusLayCSVHasBeenUploaded($messages);
                $this->checkCasRecProfCSVHasBeenUploaded($messages);
                $this->checkCasRecPACSVHasBeenUploaded($messages);
            }
        }

        return 0;
    }

    private function checkCasRecLayCSVHasBeenUploaded(array $events)
    {
        $casRecLayCSVUploads = preg_grep('/"source":"casrec","role_type":"LAY"/', $events);

        if (empty($casRecLayCSVUploads)) {
            $this->postSlackMessage(sprintf(self::CSV_NOT_UPLOADED_SLACK_MESSAGE, self::CASREC_LAY_CSV));
        }
    }

    private function checkSiriusLayCSVHasBeenUploaded(array $events)
    {
        $siriusCSVUploads = preg_grep('/"source":"sirius","role_type":"LAY"/', $events);

        if (empty($siriusCSVUploads)) {
            $this->postSlackMessage(sprintf(self::CSV_NOT_UPLOADED_SLACK_MESSAGE, self::SIRIUS_LAY_CSV));
        }
    }

    private function checkCasRecProfCSVHasBeenUploaded(array $events)
    {
        $casRecProfCSVUploads = preg_grep('/"source":"casrec","role_type":"PROF"/', $events);

        if (empty($casRecProfCSVUploads)) {
            $this->postSlackMessage(sprintf(self::CSV_NOT_UPLOADED_SLACK_MESSAGE, self::CASREC_PROF_CSV));
        }
    }

    private function checkCasRecPACSVHasBeenUploaded(array $events)
    {
        $casRecPACSVUploads = preg_grep('/"source":"casrec","role_type":"PA"/', $events);

        if (empty($casRecPACSVUploads)) {
            $this->postSlackMessage(sprintf(self::CSV_NOT_UPLOADED_SLACK_MESSAGE, self::CASREC_PA_CSV));
        }
    }

    private function postSlackMessage(string $message)
    {
        $token = $this->secretManagerService->getSecret(SecretManagerService::SLACK_APP_TOKEN_SECRET_NAME);

        $client = $this->slackClientFactory->createClient($token);

        try {
            $client->chatPostMessage(
                [
                    'username' => 'opg_response',
                    'channel' => 'opg-digideps-team',
                    'text' => $message,
                ]
            );
        } catch (Throwable $e) {
            $this->logger->log('error', sprintf('Failed to post to Slack during CSV upload check:  %s', $e->getMessage()));
        }
    }
}
