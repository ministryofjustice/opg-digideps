<?php

namespace AppBundle\Service\Audit;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\CloudWatchLogs\Exception\CloudWatchLogsException;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class AuditLogHandler extends AbstractProcessingHandler
{
    /** @var CloudWatchLogsClient */
    private $client;

    /** @var string */
    private $group;

    /** @var string */
    private $stream;

    /** @var bool */
    private $initialized = false;

    /**
     * @param CloudWatchLogsClient $client
     * @param $group
     * @param int $level
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
        $entry = $this->formatEntry($entry);

        if (false === $this->initialized) {
            $this->initialize();
        }

        // send items, retry once with a fresh sequence token
        try {
            $this->send($entry);
        } catch (CloudWatchLogsException $e) {
            $this->refreshSequenceToken();
            $this->send($entry);
        }
    }

    /**
     * @param array $record
     * @return bool
     */
    private function shallHandle(array $record): bool
    {
        return
            isset($record['context']['event']) &&
            isset($record['formatted']) &&
            isset($record['datetime']) &&
            $record['datetime'] instanceof \DateTimeInterface;
    }

    /**
     * @param array $entry
     * @return array
     */
    private function formatEntry(array $entry): array
    {
        return [
            [
                'message' => $entry['formatted'],
                'timestamp' => $entry['datetime']->format('U.u') * 1000
            ]
        ];
    }

    private function initialize(): void
    {
        $this->initializeGroup();
        $this->refreshSequenceToken();
    }

    private function initializeGroup(): void
    {
        $existingGroups = $this->fetchExistingLogGroups();
        $existingGroupsNames = $this->extractExistingGroupNames($existingGroups);

        if (!in_array($this->group, $existingGroupsNames, true)) {
            $this->createLogGroup();
        }
    }

    /**
     * @return array
     */
    private function fetchExistingLogGroups(): array
    {
        return $this
            ->client
            ->describeLogGroups(['logGroupNamePrefix' => $this->group])
            ->get('logGroups');
    }

    /**
     * @param array $existingGroups
     * @return array
     */
    private function extractExistingGroupNames(array $existingGroups): array
    {
        return array_map(
            function ($group) {
                return $group['logGroupName'];
            },
            $existingGroups
        );
    }

    private function createLogGroup(): void
    {
        $this
            ->client
            ->createLogGroup(['logGroupName' => $this->group]);
    }

    private function refreshSequenceToken(): void
    {
        $existingStreams = $this->fetchExistingStreams();
        $existingStreamsNames = $this->extractExistingStreamNames($existingStreams);

        if (!in_array($this->stream, $existingStreamsNames, true)) {
            $this->createLogStream();
        } else {
            $this->determineSequenceToken($existingStreams);
        }

        $this->initialized = true;
    }

    /**
     * @return array
     */
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

    /**
     * @param $existingStreams
     * @return array
     */
    private function extractExistingStreamNames($existingStreams): array
    {
        return array_map(
            function ($stream) {
                return $stream['logStreamName'];
            },
            $existingStreams
        );
    }

    private function createLogStream(): void
    {
        $this
            ->client
            ->createLogStream(
                [
                    'logGroupName' => $this->group,
                    'logStreamName' => $this->stream
                ]
            );
    }

    /**
     * @param array $existingStreams
     */
    private function determineSequenceToken(array $existingStreams): void
    {
        foreach ($existingStreams as $stream) {
            if ($stream['logStreamName'] === $this->stream && isset($stream['uploadSequenceToken'])) {
                $this->sequenceToken = $stream['uploadSequenceToken'];
                break;
            }
        }
    }

    /**
     * @param array $entry
     */
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

        // Set this in memory in case the same request goes on to audit log something else - saves fetching it from AWS.
        $this->sequenceToken = $response->get('nextSequenceToken');
    }

    /**
     * @return JsonFormatter
     */
    protected function getDefaultFormatter(): JsonFormatter
    {
        return new JsonFormatter();
    }
}
