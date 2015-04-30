<?php
namespace AppBundle\Tests\Service;
use AppBundle\Service\DateFormatter;

//use AppBundle\Service\ApiClient;
use Mockery as m;
use AppBundle\Entity as EntityDir;


class DateFormatterTest extends \PHPUnit_Framework_TestCase
{
    public static function formatLastLogiProvider()
    {
        return [
            ['2015-01-29 17:09:30', 'less than a minute ago'],
            
            ['2015-01-29 17:09:00', '1 minute ago'],
            ['2015-01-29 17:07:00', '3 minutes ago'],
            
            ['2015-01-29 16:10:00', '1 hour ago'],
            ['2015-01-29 7:11:00', '10 hours ago'],
            
            ['2015-01-28 15:10:00', '28/01/2015'],
        ];
    }
    
    /**
     * @test
     * @dataProvider formatLastLogiProvider
     */
    public function formatLastLogin($input, $expected)
    {
        $date = new \DateTime($input);
        $actual = DateFormatter::formatLastLogin($date, new \DateTime('2015-01-29 17:10:00'));
        $this->assertEquals($expected, $actual);
    }
    
}