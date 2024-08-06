<?php

namespace DigidepsTests\Logger;

use App\Logger\OpgLineFormatter;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class OpgLineFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new OpgLineFormatter();

        $record = [
            'datetime' => new \DateTime('2024-06-10 12:00:00'),
            'level' => Logger::INFO,
            'level_name' => 'INFO',
            'message' => 'This is a test message',
            'channel' => 'app',
            'extra' => ['foo' => 123],
            'context' => [],
        ];

        $expectedOutput = "[\033[0;94m2024-06-10 12:00:00\033[0m] \033[0;32mapp.INFO\033[0m: This is a test message [] {\"foo\":123}";

        $formattedOutput = $formatter->format($record);

        $this->assertEquals($expectedOutput, $formattedOutput);
    }
}
