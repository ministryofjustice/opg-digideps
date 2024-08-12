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
            'service_name' => 'api',
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
            // We want the first file that doesn't have monolog in path or this file
            if (
                isset($trace['file'])
                && !str_contains($trace['file'], 'monolog')
                && !str_contains($trace['file'], 'OpgJsonFormatter.php')
            ) {
                return [
                    'file' => $trace['file'],
                    'line' => $trace['line'],
                ];
            }
        }

        return [];
    }
}
