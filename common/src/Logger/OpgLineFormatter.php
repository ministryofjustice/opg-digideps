<?php

namespace OPG\Digideps\Common\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class OpgLineFormatter extends LineFormatter
{
    public function __construct()
    {
        parent::__construct(dateFormat: "Y-m-d H:i:s");
    }

    public function format(LogRecord $record): string
    {
        $output = parent::format($record);

        // Split the log message into components
        preg_match('/^\[(.*?)\] (.*?): (.*)$/', $output, $matches);

        if (4 === count($matches)) {
            // Format date (light blue)
            $date = "\033[0;94m" . $matches[1] . "\033[0m";

            // Format log level
            $level = $matches[2];

            switch (true) {
                case str_contains($level, 'DEBUG'):
                    $level = "\033[0;36m$level\033[0m"; // Cyan
                    break;
                case str_contains($level, 'INFO') || str_contains($level, 'NOTICE'):
                    $level = "\033[0;32m$level\033[0m"; // Green
                    break;
                case str_contains($level, 'WARNING'):
                    $level = "\033[0;33m$level\033[0m"; // Orange
                    break;
                case str_contains($level, 'ERROR') || str_contains($level, 'CRITICAL'):
                    $level = "\033[0;31m$level\033[0m"; // Red
                    break;
            }

            // Concatenate the formatted components
            $output = "[$date] $level: $matches[3]";
        }

        return $output;
    }
}
