<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service;

use OPG\Digideps\Frontend\Service\StringUtils;
use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    public static function secondsToHoursMinutesProvider(): array
    {
        return [
            [3900, '1 hour and 5 minutes'],
            [3600, '1 hour'],
            [3600 * 2, '2 hours'],
            [60 * 2, '2 minutes'],
            [60, '1 minute'],
            [60 + 59, '1 minute'], // ceil
            [0, 'less than a minute'],
            [1, 'less than a minute'],
            [59, 'less than a minute'],
            [3600 * 2 + 60 * 6, '2 hours and 6 minutes'],
        ];
    }

    /**
     * @dataProvider secondsToHoursMinutesProvider
     */
    public function testSecondsToHoursMinutes(int $input, string $expected): void
    {
        self::assertEquals($expected, StringUtils::secondsToHoursMinutes($input));
    }

    public static function implodeWithDifferentLastProvider(): array
    {
        return [
            [[''], ', ', ' and ', ''],
            [['hook'], ', ', ' and ', 'hook'],
            [['hook', 'line'], ', ', ' and ', 'hook and line'],
            [['hook', 'line', 'sinker'], ', ', ' and ', 'hook, line and sinker'],
            [['£3', '£5', '£8'], '+', '=', '£3+£5=£8'],
        ];
    }

    /**
     * @dataProvider implodeWithDifferentLastProvider
     */
    public function testImplodeWithDifferentLast(array $strings, string $joiner, string $lastJoiner, string $expected): void
    {
        self::assertEquals($expected, StringUtils::implodeWithDifferentLast($strings, $joiner, $lastJoiner));
    }

    public function testCleanText(): void
    {
        $input = "  Some text\r\n\r\n\twith \rsome \tirregular   spacing\n\n\nand\n\n\nnewlines ";
        $expected = "Some text\n with \nsome irregular spacing\nand\nnewlines";

        self::assertEquals($expected, StringUtils::cleanText($input));
    }
}
