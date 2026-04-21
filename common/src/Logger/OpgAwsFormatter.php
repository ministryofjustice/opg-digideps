<?php

namespace OPG\Digideps\Common\Logger;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class OpgAwsFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        $formattedRecord = [
            'level' => $record->level->value,
            'datetime' => $record->datetime,
            'context' => $record->context,
        ];

        return "{$this->toJson($formattedRecord, true)}\n";
    }
}
