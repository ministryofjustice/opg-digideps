<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Audit;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\Result;

abstract class MockCloudWatchLogsClient extends CloudWatchLogsClient
{
    abstract public function putLogEvents(): Result;
    abstract public function createLogStream(): Result;
    abstract public function describeLogStreams(): Result;
    abstract public function getLogEvents(): Result;
}
