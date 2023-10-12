<?php

namespace App\Service\Audit;

class LocalAuditLogHandler extends AbstractAuditLogHandler
{
    protected function write(array $entry): void
    {
        if (!$this->shallHandle($entry)) {
            return;
        }

        $fh = fopen('php://stderr', 'a');
        fwrite($fh, $entry['formatted']);
        fclose($fh);
    }
}
