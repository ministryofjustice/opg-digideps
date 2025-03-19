<?php

namespace App\Service\Audit;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\CloudWatchLogs\Exception\CloudWatchLogsException;
use Aws\Result;
use Monolog\Logger;

class AwsAuditLogHandler extends AbstractAuditLogHandler
{
    /**
     * @param int  $level
     * @param bool $bubble
     * @param string $group
     */
    public function __construct(private readonly CloudWatchLogsClient $client, private $group, $level = Logger::NOTICE, $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(array $entry): void
    {
        if (!$this->shallHandle($entry)) {
            return;
        }

        $stream = $entry['context']['event'];
        $sequenceToken = $this->initialize($stream);
        $entry = $this->formatEntry($entry);

        // send items, retry once with a fresh sequence token
        try {
            $this->send($entry, $stream, $sequenceToken);
        } catch (CloudWatchLogsException $e) {
            if ('InvalidSequenceTokenException' === $e->getAwsErrorCode()) {
                $this->send($entry, $stream, $e->get('expectedSequenceToken'));
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
