<?php
namespace AppBundle\Entity;

use Mockery as m;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    
    protected function setUp()
    {
        $this->account = new Account;
    }
    
    public function tearDown()
    {
        m::close();
    }
    
    public function getCountValidTotalsProvider()
    {
        return [
            [[]                         , []                            , 0],
            [['in1'=>null]              , ['out1'=>null]                , 0],
            [['in1'=>123]               , []                            , 1],
            [['in1'=>0]                 , []                            , 1],
            [[]                         , ['out1'=>123]                 , 1],
            [[]                         , ['out1'=>0]                   , 1],
            [['in1'=>123]               , ['out1'=>123]                 , 2],
            [['in1'=>0, 'in2'=>null]    , ['out1'=>123, 'out2'=>null]   , 2],
        ];
    }
    
    /**
     * @dataProvider getCountValidTotalsProvider
     */ 
    public function testgetCountValidTotals(array $moneyIn, array $moneyOut, $expected)
    {
        $mi = [];
        foreach ($moneyIn as $id => $amount) {
            $mi[] = new AccountTransaction($id, $amount);
        }
        $this->account->setMoneyIn($mi);
        
        $mo = [];
        foreach ($moneyOut as $id => $amount) {
            $mo[] = new AccountTransaction($id, $amount);
        }
        $this->account->setMoneyOut($mo);
        
        
        $this->assertEquals($expected, $this->account->getCountValidTotals());
    }
}
