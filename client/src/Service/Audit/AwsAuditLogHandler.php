<?php

namespace App\Service\Audit;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\CloudWatchLogs\Exception\CloudWatchLogsException;
use Aws\Result;
use Monolog\Logger;

class AwsAuditLogHandler extends AbstractAuditLogHandler
{
    /** @var CloudWatchLogsClient */
    private $client;

    /** @var string */
    private $group;

    /** @var string */
    private $stream;

    /** @var string */
    private $sequenceToken;

    /** @var array */
    private $existingStreams = [];

    /**
     * @param $group
     * @param int  $level
     * @param bool $bubble
     */
    public function __construct(CloudWatchLogsClient $client, $group, $level = Logger::NOTICE, $bubble = true)
    {
        $this->client = $client;
        $this->group = $group;

        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $entry): void
    {
        if (!$this->shallHandle($entry)) {
            return;
        }

        $this->stream = $entry['context']['event'];

        $this->initialize();

        $entry = $this->formatEntry($entry);

        // send items, retry once with a fresh sequence token
        try {
            $this->send($entry);
        } catch (CloudWatchLogsException $e) {
            $this->determineSequenceToken();
            $this->send($entry);
        }
    }

    private function formatEntry(array $entry): array
    {
        return [
            [
                'message' => $entry['formatted'],
                'timestamp' => $entry['datetime']->format('U.u') * 1000,
            ],
        ];
    }

    private function initialize(): void
    {
        $this->existingStreams = $this->fetchExistingStreams();
        $existingStreamsNames = $this->extractExistingStreamNames();

        if (!in_array($this->stream, $existingStreamsNames, true)) {
            $this->createLogStream();
        } else {
            $this->determineSequenceToken();
        }
    }

    private function fetchExistingStreams(): array
    {
        return $this
            ->client
            ->describeLogStreams(
                [
                    'logGroupName' => $this->group,
                    'logStreamNamePrefix' => $this->stream,
                ]
            )->get('logStreams');
    }

    private function extractExistingStreamNames(): array
    {
        return array_map(
            function ($stream) {
                return $stream['logStreamName'] ?? null;
            },
            $this->existingStreams
        );
    }

    private function createLogStream(): void
    {
        $this
            ->client
            ->createLogStream(
                [
                    'logGroupName' => $this->group,
                    'logStreamName' => $this->stream,
                ]
            );
    }

    private function determineSequenceToken(): void
    {
        $response = $this
            ->client
            ->describeLogStreams(
                [
                    'logGroupName' => $this->group,
                    'logStreamNamePrefix' => $this->stream,
                ]
            );

        $nextToken = !empty($response->get('nextToken')) ? $response->get('nextToken') : null;

        if (!$nextToken) {
            foreach ($this->existingStreams as $stream) {
                if ($stream['logStreamName'] === $this->stream && isset($stream['uploadSequenceToken'])) {
                    $this->sequenceToken = $stream['uploadSequenceToken'];
                    break;
                }
            }
        } else {
            $this->sequenceToken = $nextToken;
        }
    }

    private function send(array $entry): void
    {
        $data = [
            'logGroupName' => $this->group,
            'logStreamName' => $this->stream,
            'logEvents' => $entry,
        ];

        if (!empty($this->sequenceToken)) {
            $data['sequenceToken'] = $this->sequenceToken;
        }

        $response = $this->client->putLogEvents($data);

        // Set this in memory in case the same request goes on to audit log something else - saves fetching it from AWS.
        $this->sequenceToken = $response->get('nextSequenceToken');
    }

    public function getLogEventsByLogStream(string $streamName, int $logStartTime, int $logEndTime, string $groupName): Result
    {
        return $this->client->getLogEvents(
            [
                'logGroupName' => $groupName,
                'logStreamName' => $streamName,
                'startTime' => $logStartTime,
                'endTime' => $logEndTime,
            ]
        );
    }

    public function getLogStreams(string $logGroupName)
    {
        return $this
            ->client
            ->describeLogStreams(
                [
                    'logGroupName' => $logGroupName,
                ]
            )->get('logStreams');
    }
}
