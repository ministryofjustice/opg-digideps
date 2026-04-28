<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Common\Unit\Logger;

use Monolog\Level;
use Monolog\LogRecord;
use OPG\Digideps\Common\Logger\OpgLineFormatter;
use PHPUnit\Framework\TestCase;

final class OpgLineFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $formatter = new OpgLineFormatter();

        $record = new LogRecord(
            new \DateTimeImmutable('2024-06-10 12:00:00'),
            'app',
            Level::Info,
            'This is a test message',
            [],
            ['foo' => 123],
        );

        $expectedOutput = "[\033[0;94m2024-06-10 12:00:00\033[0m] \033[0;32mapp.INFO\033[0m: This is a test message [] {\"foo\":123}";

        $formattedOutput = $formatter->format($record);

        $this->assertEquals($expectedOutput, $formattedOutput);
    }
}
