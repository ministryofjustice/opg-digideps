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

    public function getTypeBasedOnTypeofRepAndCorrefProvider()
    {
        // follow order in https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
        return [
            // 103
            ['p3', 'opg103', CasRec::REALM_LAY,  Report::LAY_PFA_LOW_ASSETS_TYPE],
            ['p3g', 'opg103', CasRec::REALM_LAY,  Report::LAY_PFA_LOW_ASSETS_TYPE],
            ['l3', 'opg103', CasRec::REALM_LAY,  Report::LAY_PFA_LOW_ASSETS_TYPE],
            ['l3g', 'opg103', CasRec::REALM_LAY,  Report::LAY_PFA_LOW_ASSETS_TYPE],
            ['a3', 'opg103', CasRec::REALM_LAY,  Report::LAY_PFA_LOW_ASSETS_TYPE],

            // 102
            [null, null, CasRec::REALM_LAY, Report::TYPE_102],
            [null, 'opg103', CasRec::REALM_LAY, Report::TYPE_102],
            [null, 'opg103', CasRec::REALM_LAY, Report::TYPE_102],
            ['p2', 'opg102', CasRec::REALM_LAY, Report::TYPE_102],
            ['p2a', 'opg102', CasRec::REALM_LAY, Report::TYPE_102],
            ['l2a', 'opg102', CasRec::REALM_LAY, Report::TYPE_102],
            ['l2', 'opg102', CasRec::REALM_LAY, Report::TYPE_102],

            // 104
            ['hw', '', CasRec::REALM_LAY, Report::TYPE_104],

            // 103-4
            ['hw', 'opg103', CasRec::REALM_LAY, Report::TYPE_103_4],

            // 102-4
            ['hw', 'opg102', CasRec::REALM_LAY,  Report::TYPE_102_4],

            // ============ PA =============
            // 103-6
            ['a3', 'opg103', CasRec::REALM_PA, Report::TYPE_103_6],
            // 102-6
            [null, null, CasRec::REALM_PA, Report::TYPE_102_6],
            [null, 'opg103', CasRec::REALM_PA, Report::TYPE_102_6],
            [null, 'opg103', CasRec::REALM_PA, Report::TYPE_102_6],
            ['a2', 'opg102', CasRec::REALM_PA, Report::TYPE_102_6],
            ['a2a', 'opg102', CasRec::REALM_PA, Report::TYPE_102_6],
            // 104-6
            ['hw', '', CasRec::REALM_PA, Report::TYPE_104_6],
            // 103-4-6
            ['hw', 'opg103', CasRec::REALM_PA, Report::TYPE_103_4_6],
            // 102-4-6
            ['hw', 'opg102', CasRec::REALM_PA, Report::TYPE_102_4_6],

            // ============ Prof =============
            // 103-5
            ['p3', 'opg103', CasRec::REALM_PROF, Report::TYPE_103_5],
            ['p3g', 'opg103', CasRec::REALM_PROF, Report::TYPE_103_5],
            // 102-5
            [null, null, CasRec::REALM_PROF, Report::TYPE_102_5],
            [null, 'opg103', CasRec::REALM_PROF, Report::TYPE_102_5],
            [null, 'opg103', CasRec::REALM_PROF, Report::TYPE_102_5],
            ['p2', 'whatever', CasRec::REALM_PROF, Report::TYPE_102_5],
            ['p2a', 'whatever', CasRec::REALM_PROF, Report::TYPE_102_5],
            // 104-5
            ['hw', '', CasRec::REALM_PROF, Report::TYPE_104_5],
            // 103-4-5
            ['hw', 'opg103', CasRec::REALM_PROF, Report::TYPE_103_4_5],
            // 102-4-5
            ['hw', 'opg102', CasRec::REALM_PROF, Report::TYPE_102_4_5],
            ['hw', 'opg102', CasRec::REALM_PROF, Report::TYPE_102_4_5],
        ];
    }

    /**
     * @dataProvider getTypeBasedOnTypeofRepAndCorrefProvider
     */
    public function testgetTypeBasedOnTypeofRepAndCorref($corref, $typeOfRep, $realm, $expectedType)
    {
        $this->assertEquals($expectedType, CasRec::getTypeBasedOnTypeofRepAndCorref($typeOfRep, $corref, $realm));
    }
}
