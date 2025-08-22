<?php

namespace App\Service\Audit;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\CloudWatchLogs\Exception\CloudWatchLogsException;
use Aws\Result;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class AwsAuditLogHandler extends AbstractProcessingHandler
{
    /** @var CloudWatchLogsClient */
    private $client;

    /** @var string */
    private $group;

    /**
     * @param int  $level
     * @param bool $bubble
     */
    public function __construct(CloudWatchLogsClient $client, $group, $level = Logger::NOTICE, $bubble = true)
    {
        $this->client = $client;
        $this->group = $group;

        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        if (!$this->shallHandle($record)) {
            return;
        }

        $stream = $record['context']['event'];
        $sequenceToken = $this->initialize($stream);
        $record = $this->formatEntry($record);

        // send items, retry once with a fresh sequence token
        try {
            $this->send($record, $stream, $sequenceToken);
        } catch (CloudWatchLogsException $e) {
            if ('InvalidSequenceTokenException' === $e->getAwsErrorCode()) {
                $this->send($record, $stream, $e->get('expectedSequenceToken'));
            }

            throw $e;
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

    private function initialize(string $stream): ?string
    {
        $describeStreamsResponse = $this->describeStreams($stream);

        $existingStreams = $describeStreamsResponse->get('logStreams');
        $existingStreamsNames = $this->extractExistingStreamNames($existingStreams);

        if (!in_array($stream, $existingStreamsNames, true)) {
            $this->createLogStream($stream);
        } else {
            return $existingStreams[0]['uploadSequenceToken'];
        }

        return null;
    }

    private function describeStreams(string $stream): Result
    {
        return $this
            ->client
            ->describeLogStreams(
                [
                    'logGroupName' => $this->group,
                    'logStreamNamePrefix' => $stream,
                ]
            );
    }

    private function extractExistingStreamNames(array $streams): array
    {
        return array_map(
            function ($stream) {
                return $stream['logStreamName'] ?? null;
            },
            $streams
        );
    }

    private function createLogStream(string $stream): void
    {
        $this
            ->client
            ->createLogStream(
                [
                    'logGroupName' => $this->group,
                    'logStreamName' => $stream,
                ]
            );
    }

    private function send(array $entry, string $stream, ?string $sequenceToken): void
    {
        $data = [
            'logGroupName' => $this->group,
            'logStreamName' => $stream,
            'logEvents' => $entry,
        ];

        if (!empty($sequenceToken)) {
            $data['sequenceToken'] = $sequenceToken;
        }

        $this->client->putLogEvents($data);
    }

    private function shallHandle(array $record): bool
    {
        return
            isset($record['context']['event'])
            && isset($record['context']['type'])
            && 'audit' === $record['context']['type'];
    }

    private function getDefaultFormatter(): JsonFormatter
    {
        return new JsonFormatter();
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
