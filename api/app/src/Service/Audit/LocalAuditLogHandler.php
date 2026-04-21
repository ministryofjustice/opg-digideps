<?php

namespace App\Service\Audit;

use Monolog\LogRecord;

class LocalAuditLogHandler extends AbstractAuditLogHandler
{
    protected function write(LogRecord $record): void
    {
        $formatted = $record->formatted;
        if (!$this->shallHandle($record) || !is_string($formatted)) {
            return;
        }

        $fh = fopen('php://stderr', 'a');
        fwrite($fh, $formatted);
        fclose($fh);
    }
}
