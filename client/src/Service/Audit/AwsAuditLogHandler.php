<?php

namespace App\Service\Audit;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\CloudWatchLogs\Exception\CloudWatchLogsException;
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

    /** @var bool */
    private $initialized = false;

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
        $entry = $this->formatEntry($entry);

        if (false === $this->initialized) {
            $this->initialize();
        }

        // send items, retry once with a fresh sequence token
        try {
            $this->send($entry);
        } catch (CloudWatchLogsException $e) {
            $this->determineSequenceToken($refresh = true);
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

        $this->initialized = true;
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
                return $stream['logStreamName'];
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

    private function determineSequenceToken(bool $refresh = false): void
    {
        if ($refresh) {
            $this->existingStreams = $this->fetchExistingStreams();
        }

        foreach ($this->existingStreams as $stream) {
            if ($stream['logStreamName'] === $this->stream && isset($stream['uploadSequenceToken'])) {
                $this->sequenceToken = $stream['uploadSequenceToken'];
                break;
            }
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
}
