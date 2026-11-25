<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Command;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Service\Audit\AwsAuditLogHandler;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use Aws\Exception\AwsException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCSVUploadedCommand extends DaemonableCommand
{
    public const string CSV_NOT_UPLOADED_MESSAGE = 'check_csv_uploaded - failure - The %s CSV has not been uploaded within the past 24 hours';
    public const string FAILED_TO_RETRIEVE_BANK_HOLIDAYS_MESSAGE = 'check_csv_uploaded - failure - Failed to retrieve bank holidays from Gov.uk. Error message: %s';
    public const string FAILED_TO_RETRIEVE_AUDIT_LOG_MESSAGE = 'check_csv_uploaded - failure - Failed to retrieve audit logs during CSV upload check. Error message: %s';
    public const string LOG_GROUP_NOT_CREATED_MESSAGE = 'check_csv_uploaded - failure - A log group with the name "%s" could not be found. Unable to determine if CSVs have been uploaded.';
    public const string UNEXPECTED_ERROR_MESSAGE = 'check_csv_uploaded - failure - An unexpected error occurred during CSV upload check. Error message: %s';

    public const string LAY_CSV = 'LAY';
    public const string ORG_CSV = 'ORG';

    /** @var string|null $defaultName*/
    public static $defaultName = 'digideps:check-csv-uploaded';

    private \DateTime $now;

    public function __construct(
        private readonly DateTimeProvider $dateTimeProvider,
        private readonly AwsAuditLogHandler $awsAuditLogHandler,
        private readonly LoggerInterface $logger,
        private readonly string $auditLogGroupName
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
            $path = realpath(__DIR__ . '/../../bank-holidays.json') ?: throw new \LogicException('bank-holidays.json not found');
            $rawDates = file_get_contents($path) ?: throw new \LogicException('Could not read bank-holidays.json');
            $dates = new ValidatingArray((array)json_decode($rawDates, true));
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf(self::FAILED_TO_RETRIEVE_BANK_HOLIDAYS_MESSAGE, $e->getMessage()));

            return 1;
        }

        $events = $dates->getValidatingArrayOrNull('england-and-wales')?->getArrayOrNull('events') ?? [];
        $isBankHoliday = in_array($currentDate, array_column($events, 'date'));

        // Do not alert on Bank Holidays
        if ($isBankHoliday === false) {
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
        $startingTime = (int) (clone $this->now)->sub(new \DateInterval('P1D'))->format('Uv');
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
            if ($e->getAwsErrorCode() === 'ResourceNotFoundException') {
                $this->alertNoCSVsWereUploaded();

                return 0;
            } else {
                $this->logger->warning(sprintf(self::FAILED_TO_RETRIEVE_AUDIT_LOG_MESSAGE, $e->getMessage()));

                return 1;
            }
        }

        return $logEvents;
    }

    private function getLogStreams(): mixed
    {
        try {
            return $this->awsAuditLogHandler->getLogStreams($this->auditLogGroupName);
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() === 'ResourceNotFoundException') {
                $this->logger->warning(sprintf(self::LOG_GROUP_NOT_CREATED_MESSAGE, $this->auditLogGroupName));
            } else {
                $this->logger->warning(sprintf(self::UNEXPECTED_ERROR_MESSAGE, $e->getAwsErrorMessage()));
            }
        }
        return null;
    }

    private function alertNoCSVsWereUploaded(): void
    {
        foreach ([self::LAY_CSV, self::ORG_CSV] as $csvType) {
            $this->logger->error(sprintf(self::CSV_NOT_UPLOADED_MESSAGE, $csvType));
        }
    }

    private function checkCsvsHaveBeenUploadedAndAlert(array $events): void
    {
        $typesAndRegexes = [
            self::LAY_CSV => '/"role_type":"LAY"/',
            self::ORG_CSV => '/"role_type":"ORG"/',
        ];

        foreach ($typesAndRegexes as $type => $regex) {
            $uploadEvents = preg_grep($regex, $events);

            if (empty($uploadEvents)) {
                $this->logger->error(sprintf(self::CSV_NOT_UPLOADED_MESSAGE, $type));
            }
        }
    }
}
