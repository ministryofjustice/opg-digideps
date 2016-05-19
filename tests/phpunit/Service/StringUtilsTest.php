<?php

namespace AppBundle\Service;


class StringUtilsTest extends \PHPUnit_Framework_TestCase
{
    public static function secondsToHoursMinutesProvider()
    {
        return [
            [3900, '1 hour and 5 minutes'],
            [3600, '1 hour'],
            [3600*2, '2 hours'],
            [60*2, '2 minutes'],
            [60, '1 minute'],
            [60+59, '1 minute'], //ceil
            [0, 'less than a minute'],
            [1, 'less than a minute'],
            [59, 'less than a minute'],
            [3600 * 2 + 60*6, '2 hours and 6 minutes'],
            
        ];
    }

    /**
     * @test
     * @dataProvider secondsToHoursMinutesProvider
     */
    public function secondsToHoursMinutes($input, $expected)
    {
        $this->assertEquals($expected, StringUtils::secondsToHoursMinutes($input));
    }

}
