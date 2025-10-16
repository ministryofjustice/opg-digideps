<?php

declare(strict_types=1);

namespace DigidepsTests\Logger;

use DateTime;
use App\Logger\OpgJsonFormatter;
use PHPUnit\Framework\TestCase;

final class OpgJsonFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $formatter = new OpgJsonFormatter();

        // Mock $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['HTTP_X_AWS_REQUEST_ID'] = '12345';
        $_SERVER['HTTP_X_SESSION_SAFE_ID'] = '98765';

        // Create a sample log record
        $record = [
            'datetime' => new DateTime('2024-06-10 12:00:00'),
            'level_name' => 'INFO',
            'message' => 'This is a test message',
        ];

        $expectedOutput = '{
            "time": "2024-06-10T12:00:00Z",
            "level": "INFO",
            "msg": "This is a test message",
            "service_name": "api",
            "request": { "method": "GET", "path": "/test", "aws_request_id":"12345","session_safe_id":"98765" },
            "location": { "file": "' . __FILE__ . '", "line": ' . (__LINE__ + 3) . '}
        }';
        // Careful of spacing here as __LINE__ + 3 above needs to be the below line!
        $formattedOutput = $formatter->format($record);

        // Remove whitespace and newlines from expected output for comparison
        $expectedOutput = preg_replace('/\s+/', '', $expectedOutput);
        // Remove whitespace and newlines from actual output for comparison
        $formattedOutput = preg_replace('/\s+/', '', $formattedOutput);

        $this->assertEquals($expectedOutput, $formattedOutput);
    }
}
