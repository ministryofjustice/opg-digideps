<?php

namespace App\Service;

use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    public static function secondsToHoursMinutesProvider()
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
     * @test
     *
     * @dataProvider secondsToHoursMinutesProvider
     */
    public function secondsToHoursMinutes($input, $expected)
    {
        $this->assertEquals($expected, StringUtils::secondsToHoursMinutes($input));
    }

    public static function implodeWithDifferentLastProvider()
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
     * @test
     *
     * @dataProvider implodeWithDifferentLastProvider
     */
    public function implodeWithDifferentLast($strings, $joiner, $lastJoiner, $expected)
    {
        $this->assertEquals($expected, StringUtils::implodeWithDifferentLast($strings, $joiner, $lastJoiner));
    }

    public function testCleanText()
    {
        $input = "  Some text\r\n\r\n\twith \rsome \tirregular   spacing\n\n\nand\n\n\nnewlines ";
        $expected = "Some text\n with \nsome irregular spacing\nand\nnewlines";

        $this->assertEquals($expected, StringUtils::cleanText($input));
    }
}
