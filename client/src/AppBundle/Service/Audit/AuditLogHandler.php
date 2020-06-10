<?php

namespace AppBundle\Service\Audit;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\CloudWatchLogs\Exception\CloudWatchLogsException;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class AuditLogHandler extends AbstractProcessingHandler
{
    /**
     * Event size limit (https://docs.aws.amazon.com/AmazonCloudWatch/latest/logs/cloudwatch_limits_cwl.html)
     *
     * @var int
     */
    const EVENT_SIZE_LIMIT = 262118; // 262144 - reserved 26

    const AUDIT_LOG_ENV_VAR = 'AUDIT_LOG_GROUP_NAME';

    /** @var CloudWatchLogsClient */
    private $client;

    /** @var string */
    private $group;

    /** @var string */
    private $stream;

    /** @var integer */
    private $retention;

    /** @var bool */
    private $createGroup;

    /** @var bool */
    private $initialized = false;

    public function __construct(
        CloudWatchLogsClient $client,
        $retention = 14,
        $level = Logger::NOTICE,
        $bubble = true,
        $createGroup = true
    ) {
        $this->client = $client;
        $this->group = getenv(self::AUDIT_LOG_ENV_VAR);
        $this->retention = $retention;
        $this->createGroup = $createGroup;

        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        putenv('AWS_ACCESS_KEY_ID=ASIATT3PESUZPEX67TNP');
        putenv('AWS_SECRET_ACCESS_KEY=P9FPxHTMk4jZGNI0QGWTyhP3L0fL6rmZZ91Asi8y');
        putenv('AWS_SESSION_TOKEN=FwoGZXIvYXdzEK///////////wEaDAoYC4OymJBI8Yj23iK7AdHpKcS4bfG2Ry96JRof7MVZ/+Z1rTdWdE+tI7nofGKNM29yo+6yZRnnuEBsqHuDsA9KomsEq8E+A4u3rIaJbOXGlG3RNcAUIFQck5YoRNbof/U7wz2RpP4gIfhLH27ojIPEZEeZuAAT5bnikW/8XXXJj+zCeW4tXQQ25OhOq4uJhbJx4Bla3w1aFsw/g8reBuyIk5SZFoNxTp64ccz6tyqX5BRlq5ECX39qkttacgW1L3pDXqJ+1o8ZWEMokZX+9gUyLdqreDCGAuDlU0LQjPHc5VDpjM1m0VN1meo7p/8Mh1hnxY3A8wiG72im71oVvA==');

        if (!isset($record['context']['event'])) {
            return;
        }

        $this->stream = $record['context']['event'];
        $records = $this->formatRecords($record);

        if (false === $this->initialized) {
            $this->initialize();
        }

        // send items, retry once with a fresh sequence token
        try {
            $this->send($records);
        } catch (CloudWatchLogsException $e) {
            $this->refreshSequenceToken();
            $this->send($records);
        }
    }

    private function formatRecords(array $entry): array
    {
        $entries = str_split($entry['formatted'], self::EVENT_SIZE_LIMIT);
        $timestamp = $entry['datetime']->format('U.u') * 1000;
        $records = [];

        foreach ($entries as $entry) {
            $records[] = [
                'message' => $entry,
                'timestamp' => $timestamp
            ];
        }

        return $records;
    }

    private function initialize(): void
    {
        if ($this->createGroup) {
            $this->initializeGroup();
        }

        $this->refreshSequenceToken();
    }

    private function initializeGroup(): void
    {
        // fetch existing groups
        $existingGroups =
            $this
                ->client
                ->describeLogGroups(['logGroupNamePrefix' => $this->group])
                ->get('logGroups');

        // extract existing groups names
        $existingGroupsNames = array_map(
            function ($group) {
                return $group['logGroupName'];
            },
            $existingGroups
        );

        // create group and set retention policy if not created yet
        if (!in_array($this->group, $existingGroupsNames, true)) {
            $createLogGroupArguments = ['logGroupName' => $this->group];

            if (!empty($this->tags)) {
                $createLogGroupArguments['tags'] = $this->tags;
            }

            $this
                ->client
                ->createLogGroup($createLogGroupArguments);

            if ($this->retention !== null) {
                $this
                    ->client
                    ->putRetentionPolicy(
                        [
                            'logGroupName' => $this->group,
                            'retentionInDays' => $this->retention,
                        ]
                    );
            }
        }
    }


    private function refreshSequenceToken(): void
    {
        // fetch existing streams
        $existingStreams =
            $this
                ->client
                ->describeLogStreams(
                    [
                        'logGroupName' => $this->group,
                        'logStreamNamePrefix' => $this->stream,
                    ]
                )->get('logStreams');

        // extract existing streams names
        $existingStreamsNames = array_map(
            function ($stream) {

                // set sequence token
                if ($stream['logStreamName'] === $this->stream && isset($stream['uploadSequenceToken'])) {
                    $this->sequenceToken = $stream['uploadSequenceToken'];
                }

                return $stream['logStreamName'];
            },
            $existingStreams
        );

        // create stream if not created
        if (!in_array($this->stream, $existingStreamsNames, true)) {
            $this
                ->client
                ->createLogStream(
                    [
                        'logGroupName' => $this->group,
                        'logStreamName' => $this->stream
                    ]
                );
        }

        $this->initialized = true;
    }

    private function send(array $entry): void
    {
        $data = [
            'logGroupName' => $this->group,
            'logStreamName' => $this->stream,
            'logEvents' => $entry
        ];

        if (!empty($this->sequenceToken)) {
            $data['sequenceToken'] = $this->sequenceToken;
        }

        $response = $this->client->putLogEvents($data);

        $this->sequenceToken = $response->get('nextSequenceToken');
    }

    protected function getDefaultFormatter()
    {
        return new JsonFormatter();
    }
}
