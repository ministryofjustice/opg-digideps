<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="casrec")
 * @ORM\Entity
 */
class CasRecTest extends \PHPUnit_Framework_TestCase
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
        return [
            // 102
            [null, null, User::ROLE_LAY_DEPUTY, Report::TYPE_102],
            [null, 'opg103', User::ROLE_LAY_DEPUTY, Report::TYPE_102],
            [null, 'opg103', User::ROLE_LAY_DEPUTY, Report::TYPE_102],
            ['l3', 'whatever', User::ROLE_LAY_DEPUTY, Report::TYPE_102],
            ['l3g', 'whatever', User::ROLE_LAY_DEPUTY, Report::TYPE_102],
            ['a3', 'whatever', User::ROLE_LAY_DEPUTY, Report::TYPE_102],
            ['l2', 'opg103', User::ROLE_LAY_DEPUTY, Report::TYPE_102],
            ['hw', 'opg103', User::ROLE_LAY_DEPUTY, Report::TYPE_102],
            ['hw', 'opg102', User::ROLE_LAY_DEPUTY, Report::TYPE_102],

            // 103
            ['l3', 'opg103', User::ROLE_LAY_DEPUTY, Report::ENABLE_103 ? Report::TYPE_103 : Report::TYPE_102],
            ['l3g', 'opg103', User::ROLE_LAY_DEPUTY, Report::ENABLE_103 ? Report::TYPE_103 : Report::TYPE_102],
            ['a3', 'opg103', User::ROLE_LAY_DEPUTY, Report::ENABLE_103 ? Report::TYPE_103 : Report::TYPE_103],

            // 104
            ['hw', '', User::ROLE_LAY_DEPUTY, Report::ENABLE_104 ? Report::TYPE_104 : Report::TYPE_102],

            // 102-6
            [null, null, User::ROLE_PA, Report::TYPE_102_6],
            [null, 'opg103', User::ROLE_PA, Report::TYPE_102_6],
            [null, 'opg103', User::ROLE_PA, Report::TYPE_102_6],
            ['l3', 'whatever', User::ROLE_PA, Report::TYPE_102_6],
            ['l3g', 'whatever', User::ROLE_PA, Report::TYPE_102_6],
            ['a3', 'whatever', User::ROLE_PA, Report::TYPE_102_6],
            ['l2', 'opg103', User::ROLE_PA, Report::TYPE_102_6],
            ['hw', 'opg103', User::ROLE_PA, Report::TYPE_102_6],
            ['hw', 'opg102', User::ROLE_PA, Report::TYPE_102_6],

            // 103-6
            ['l3', 'opg103', User::ROLE_PA, Report::ENABLE_103 ? Report::TYPE_103_6 : Report::TYPE_102_6],
            ['l3g', 'opg103', User::ROLE_PA, Report::ENABLE_103 ? Report::TYPE_103_6 : Report::TYPE_102_6],
            ['a3', 'opg103', User::ROLE_PA, Report::ENABLE_103 ? Report::TYPE_103_6 : Report::TYPE_103_6],

            // 104-6
            ['hw', '', User::ROLE_PA, Report::ENABLE_104 ? Report::TYPE_104_6 : Report::TYPE_102_6],
        ];
    }

    /**
     * @dataProvider getTypeBasedOnTypeofRepAndCorrefProvider
     */
    public function testgetTypeBasedOnTypeofRepAndCorref($corref, $typeOfRep, $userRoleName, $expectedType)
    {
        $this->assertEquals($expectedType, CasRec::getTypeBasedOnTypeofRepAndCorref($typeOfRep, $corref, $userRoleName));
    }
}
