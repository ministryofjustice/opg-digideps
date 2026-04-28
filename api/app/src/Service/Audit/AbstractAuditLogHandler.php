<?php

namespace OPG\Digideps\Backend\Service\Audit;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

abstract class AbstractAuditLogHandler extends AbstractProcessingHandler
{
    protected function shallHandle(LogRecord $record): bool
    {
        return
            isset($record->context['event'])
            && isset($record->context['type'])
            && 'audit' === $record->context['type'];
    }

    protected function getDefaultFormatter(): JsonFormatter
    {
        return new JsonFormatter();
    }
}
