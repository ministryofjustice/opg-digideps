<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\PaService;

class PaServiceStaticTest extends \PHPUnit_Framework_TestCase
{
    public static function parseDateProvider()
    {
        return [
            // d-M-
            ['05-Feb-15', '2015-02-05'],
            ['23-May-17', '2017-05-23'],
            ['15-Jul-17', '2017-07-15'],
            ['10-Jul-17', '2017-07-10'],

            //d/m/Y format
            ['20-MAR-2003', '2003-03-20'],
            ['29-MAY-2013', '2013-05-29'],
            ['07-OCT-2016', '2016-10-07'],

            // invalid date
            ['07-xxx-2016', false],
        ];
    }

    /**
     * @dataProvider parseDateProvider
     */
    public function testparseDate($in, $expectedYmd)
    {
        $actual = PaService::parseDate($in);

        $this->assertEquals($expectedYmd, $actual ? $actual->format('Y-m-d'): $actual);
    }
}
