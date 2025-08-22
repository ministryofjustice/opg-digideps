<?php

namespace App\Service\Audit;

class LocalAuditLogHandler extends AbstractAuditLogHandler
{
    protected function write(array $record): void
    {
        if (!$this->shallHandle($record)) {
            return;
        }

        $fh = fopen('php://stderr', 'a');
        fwrite($fh, $record['formatted']);
        fclose($fh);
    }
}
