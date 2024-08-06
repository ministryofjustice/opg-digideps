<?php

namespace DigidepsTests\Logger;

use App\Logger\OpgJsonFormatter;
use PHPUnit\Framework\TestCase;

class OpgJsonFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new OpgJsonFormatter();

        // Mock $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';

        // Create a sample log record
        $record = [
            'datetime' => new \DateTime('2024-06-10 12:00:00'),
            'level_name' => 'INFO',
            'message' => 'This is a test message',
        ];

        $expectedOutput = '{
            "time": "2024-06-10T12:00:00Z",
            "level": "INFO",
            "msg": "This is a test message",
            "service_name": "api",
            "request": { "method": "GET", "path": "/test" },
            "location": { "file": "'.__FILE__.'", "line": '.(__LINE__ + 3).'}
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
