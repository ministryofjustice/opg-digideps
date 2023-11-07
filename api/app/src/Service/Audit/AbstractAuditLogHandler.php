<?php

namespace App\Service\Audit;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;

abstract class AbstractAuditLogHandler extends AbstractProcessingHandler
{
    protected function shallHandle(array $record): bool
    {
        return
            isset($record['context']['event'])
            && isset($record['context']['type'])
            && 'audit' === $record['context']['type'];
    }

    protected function getDefaultFormatter(): JsonFormatter
    {
        return new JsonFormatter();
    }
}
