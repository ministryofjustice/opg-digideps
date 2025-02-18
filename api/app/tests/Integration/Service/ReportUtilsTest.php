<?php

namespace App\Tests\Integration\Service;

use App\Service\ReportUtils;
use PHPUnit\Framework\TestCase;

class ReportUtilsTest extends TestCase
{
    public static function parseDateProvider()
    {
        return [
            // d-M-y 20xx
            ['05-Feb-15', '2015-02-05', '20'],
            ['23-May-17', '2017-05-23', '20'],
            ['15-Jul-17', '2017-07-15', '20'],
            ['10-Jul-17', '2017-07-10', '20'],
            ['10-Jul-98', '2098-07-10', '20'],
            ['10-Jul-00', '2000-07-10', '20'],

            // d-M-y 19xx
            ['10-Jul-47', '1947-07-10', '19'],
            ['10-Jul-65', '1965-07-10', '19'],
            ['10-Jul-00', '1900-07-10', '19'],

            // d/m/Y format
            ['20-MAR-2003', '2003-03-20', '20'],
            ['29-MAY-2013', '2013-05-29', '20'],
            ['07-OCT-2016', '2016-10-07', '20'],
            ['07-OCT-1945', '1945-10-07', '20'], // third param ignored if full year is given

            // invalid days
            ['00-MAY-2016', false, '20'],
            ['32-MAY-2016', false, '20'],
            // invalid month
            ['07-xxx-2016', false, '20'],
            ['07-janu-2016', false, '20'],
            ['07-00-2016', false, '20'],
            ['07-01-2016', false, '20'],
            // invalid year
            ['01-JAN-0', false, '20'],
            ['01-JAN-1', false, '20'],
            ['01-JAN-000', false, '20'],
            ['01-JAN-001', false, '20'],
            ['01-JAN-00000', false, '20'],
            ['01-JAN-00001', false, '20'],
        ];
    }

    /**
     * Data provider for reporting periods, end date and expected start date.
     *
     * @return array
     */
    public function reportPeriodDateProvider()
    {
        return [
            ['2010-01-01', '2009-01-02'],
            ['2010-02-28', '2009-03-01'],
            ['2010-12-31', '2010-01-01'],
            ['2011-01-01', '2010-01-02'],
            ['2011-02-28', '2010-03-01'],
            ['2011-12-31', '2011-01-01'],
            ['2012-01-01', '2011-01-02'],
            ['2012-02-28', '2011-03-01'],
            ['2012-02-29', '2011-03-01'],
            ['2012-12-31', '2012-01-01'],
            ['2013-01-01', '2012-01-02'],
            ['2013-02-28', '2012-02-29'],
            ['2013-12-31', '2013-01-01'],
            ['2014-01-01', '2013-01-02'],
            ['2014-02-28', '2013-03-01'],
            ['2014-12-31', '2014-01-01'],
            ['2015-01-01', '2014-01-02'],
            ['2015-02-28', '2014-03-01'],
            ['2015-12-31', '2015-01-01'],
            ['2016-01-01', '2015-01-02'],
            ['2016-02-29', '2015-03-01'],
            ['2016-02-28', '2015-03-01'],
            ['2016-12-31', '2016-01-01'],
            ['2017-01-01', '2016-01-02'],
            ['2017-02-28', '2016-02-29'],
            ['2017-12-31', '2017-01-01'],
        ];
    }

    /**
     * @dataProvider reportPeriodDateProvider
     */
    public function testGenerateStartDateFromEndDate($endDate, $expectedStartDate)
    {
        $sut = new ReportUtils();

        $endDate = new \DateTime($endDate);
        $startDate = $sut->generateReportStartDateFromEndDate($endDate);

        $this->assertEquals($expectedStartDate, $startDate->format('Y-m-d'));
    }

    /**
     * @dataProvider parseDateProvider
     */
    public function testparseDate($in, $expectedYmd, $century)
    {
        $sut = new ReportUtils();
        $actual = $sut->parseCsvDate($in, $century);

        $this->assertEquals($expectedYmd, $actual ? $actual->format('Y-m-d') : $actual);
    }

    /**
     * @test
     *
     * @dataProvider numberProvider
     */
    public function padCasRecNumber(string $number, $expectedPaddedNumber)
    {
        $sut = new ReportUtils();
        self::assertEquals($expectedPaddedNumber, $sut->padCasRecNumber($number));
    }

    public function numberProvider()
    {
        return [
            '1 character' => ['1', '00000001'],
            '2 character' => ['ab', '000000ab'],
            '3 character' => ['1ab', '000001ab'],
            '4 character' => ['87TY', '000087TY'],
            '5 character' => ['12345', '00012345'],
            '6 character' => ['123456', '00123456'],
            '7 character' => ['1234567', '01234567'],
            '8 character' => ['12345678', '12345678'],
            '9 character' => ['123456789', '123456789'],
        ];
    }
}
