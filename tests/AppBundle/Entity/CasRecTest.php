<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Report\Report;
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
            //corref, type of rep, expected created report

            // 103 created with L3(G) - OPG103
            ['l3', 'opg103', Report::ENABLE_103 ? Report::TYPE_103 : Report::TYPE_102],
            ['l3g', 'opg103', Report::ENABLE_103 ? Report::TYPE_103 : Report::TYPE_102],

            // 104 create with
            ['hw', '', Report::ENABLE_104 ? Report::TYPE_104 : Report::TYPE_102],

            // all the rest is a 102 (default)
            [null, null, Report::TYPE_102],
            [null, 'opg103', Report::TYPE_102],
            [null, 'opg103', Report::TYPE_102],
            ['l2', 'opg103', Report::TYPE_102],
            ['hw', 'opg103', Report::TYPE_102],
            ['hw', 'opg102', Report::TYPE_102],
        ];
    }

    /**
     * @dataProvider getTypeBasedOnTypeofRepAndCorrefProvider
     */
    public function testgetTypeBasedOnTypeofRepAndCorref($corref, $typeOfRep, $expectedType)
    {
        $this->assertEquals($expectedType, CasRec::getTypeBasedOnTypeofRepAndCorref($typeOfRep, $corref));
    }
}
