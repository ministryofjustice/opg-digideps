<?php

namespace App\Logger;

use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;

class OpgJsonFormatter extends MonologJsonFormatter
{
    public function format(array $record): string
    {
        $caller = $this->getCallerInfo();
        $file = $caller['file'] ?? 'unknown file';
        $line = $caller['line'] ?? 'unknown line';

        $formattedRecord = [
            'time' => $record['datetime']->format('Y-m-d\TH:i:s\Z'),
            'level' => strtoupper($record['level_name']),
            'msg' => $record['message'],
            'service_name' => 'client',
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
                'path' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
            ],
            'location' => [
                'file' => $file,
                'line' => $line,
            ],
        ];

        return $this->toJson($formattedRecord, true).($this->appendNewline ? "\n" : '');
    }

    private function getCallerInfo(): array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        foreach ($backtrace as $trace) {
            // Files go up stack trace as monolog and finally the name of this file before we get to relevant calling file
            if (isset($trace['file']) && !str_contains($trace['file'], 'monolog') && !str_contains($trace['file'], 'OpgJsonFormatter.php')) {
                return [
                    'file' => $trace['file'],
                    'line' => $trace['line'],
                ];
            }
        }

        return [];
    }
}
