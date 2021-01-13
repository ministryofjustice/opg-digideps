<?php

namespace App\Service\Audit;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;

abstract class AbstractAuditLogHandler extends AbstractProcessingHandler
{
    /**
     * @param array $record
     * @return bool
     */
    protected function shallHandle(array $record): bool
    {
        return
            isset($record['context']['event']) &&
            isset($record['context']['type']) &&
            $record['context']['type'] === 'audit';
    }

    /**
     * @return JsonFormatter
     */
    protected function getDefaultFormatter(): JsonFormatter
    {
        return new JsonFormatter();
    }
}
