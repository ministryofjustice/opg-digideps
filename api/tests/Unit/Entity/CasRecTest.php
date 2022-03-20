<?php

namespace App\Tests\Unit\Entity;

use App\Entity\CasRec;
use App\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;

/**
 * @ORM\Table(name="casrec")
 * @ORM\Entity
 */
class CasRecTest extends TestCase
{
    public static function normalizeSurnameProvider()
    {
        return [
            ['', ''],
            ['  ', ''],
            [' A !@£$1%^&*(   b!{}"| ', 'a1b'],
            [null, null],
            ['PELL M B E ', 'pell'],
            [' COGGINGS MBE ', 'coggings'],
            ["D'SOUZA GAIR", 'dsouzagair'],
            ['KNIGHT- PREISS', 'knightpreiss'],
            ['PHIPPS - THE MARQUIS OF NORMANBY', 'phippsthemarquisofnormanby'],
            // accent conversion
            ['Villanueva Cortés', 'villanuevacortes'],
            ['Ní  Fhatharta', 'nifhatharta'],
            [' Müller ', 'muller'],
        ];
    }

    /**
     * @dataProvider  normalizeSurnameProvider
     */
    public function testnormaliseSurname($input, $expected)
    {
        $this->assertEquals($expected, CasRec::normaliseSurname($input));
    }

    public static function normaliseCaseNumberProvider()
    {
        return [
            ['   12345678   ', '12345678'],
            [' 1234567T ', '1234567t'],
        ];
    }

    /**
     * @dataProvider normaliseCaseNumberProvider
     */
    public function testnormaliseCaseNumber($input, $expected)
    {
        $this->assertEquals($expected, CasRec::normaliseDeputyNo($input));
    }

    public function getReportTypeByOrderTypeProvider()
    {
        // follow order in https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
        return [
            Report::LAY_PFA_LOW_ASSETS_TYPE => ['opg103', 'pfa', CasRec::REALM_LAY,  Report::LAY_PFA_LOW_ASSETS_TYPE],
            Report::LAY_PFA_HIGH_ASSETS_TYPE => ['opg102', 'pfa', CasRec::REALM_LAY, Report::LAY_PFA_HIGH_ASSETS_TYPE],
            Report::LAY_HW_TYPE => ['opg104', 'hw', CasRec::REALM_LAY, Report::LAY_HW_TYPE],
            Report::LAY_COMBINED_LOW_ASSETS_TYPE => ['opg103', 'hw', CasRec::REALM_LAY, Report::LAY_COMBINED_LOW_ASSETS_TYPE],
            Report::LAY_COMBINED_HIGH_ASSETS_TYPE => ['opg102', 'hw', CasRec::REALM_LAY,  Report::LAY_COMBINED_HIGH_ASSETS_TYPE],
            Report::PA_PFA_LOW_ASSETS_TYPE => ['opg103', 'pfa', CasRec::REALM_PA, Report::PA_PFA_LOW_ASSETS_TYPE],
            Report::PA_PFA_HIGH_ASSETS_TYPE => ['opg102', 'pfa', CasRec::REALM_PA, Report::PA_PFA_HIGH_ASSETS_TYPE],
            Report::PA_HW_TYPE => ['opg104', 'hw', CasRec::REALM_PA, Report::PA_HW_TYPE],
            Report::PA_COMBINED_LOW_ASSETS_TYPE => ['opg103', 'hw', CasRec::REALM_PA, Report::PA_COMBINED_LOW_ASSETS_TYPE],
            Report::PA_COMBINED_HIGH_ASSETS_TYPE => ['opg102', 'hw', CasRec::REALM_PA, Report::PA_COMBINED_HIGH_ASSETS_TYPE],
            Report::PROF_PFA_LOW_ASSETS_TYPE => ['opg103', 'pfa', CasRec::REALM_PROF, Report::PROF_PFA_LOW_ASSETS_TYPE],
            Report::PROF_PFA_HIGH_ASSETS_TYPE => ['opg102', 'pfa', CasRec::REALM_PROF, Report::PROF_PFA_HIGH_ASSETS_TYPE],
            Report::PROF_HW_TYPE => ['opg104', 'hw', CasRec::REALM_PROF, Report::PROF_HW_TYPE],
            Report::PROF_COMBINED_LOW_ASSETS_TYPE => ['opg103', 'hw', CasRec::REALM_PROF, Report::PROF_COMBINED_LOW_ASSETS_TYPE],
            Report::PROF_COMBINED_HIGH_ASSETS_TYPE => ['opg102', 'hw', CasRec::REALM_PROF, Report::PROF_COMBINED_HIGH_ASSETS_TYPE],
        ];
    }

    /**
     * @dataProvider getReportTypeByOrderTypeProvider
     */
    public function testGetReportTypeByOrderType($reportType, $orderType, $realm, $expectedType)
    {
        $this->assertEquals($expectedType, CasRec::getReportTypeByOrderType($reportType, $orderType, $realm));
    }
}
