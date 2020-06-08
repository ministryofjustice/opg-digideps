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
        string $group,
        $retention = 14,
        $level = Logger::NOTICE,
        $bubble = true,
        $createGroup = true
    ) {
        $this->client = $client;
        $this->group = $group;
        $this->retention = $retention;
        $this->createGroup = $createGroup;

        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
//        putenv('AWS_ACCESS_KEY_ID=');
//        putenv('AWS_SECRET_ACCESS_KEY=');
//        putenv('AWS_SESSION_TOKEN=');

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
