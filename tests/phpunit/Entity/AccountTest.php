<?php
namespace AppBundle\Entity;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Account
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Account;
    }

    public function testSetterGetters()
    {
        $this->assertEquals('123456', $this->object->setAccountNumber('123456')->getAccountNumber());
        $this->assertEquals('123456', $this->object->setBalanceJustification('123456')->getBalanceJustification());
        $this->assertEquals('123456', $this->object->setBank('123456')->getBank());
        $this->assertEquals('123456', $this->object->setClosingBalance('123456')->getClosingBalance());
        $this->assertEquals('123456', $this->object->setOpeningBalance('123456')->getOpeningBalance());
    }
    
    /**
     * Tests is date justifiable when true
     */
    public function testIsDateJustifiableTrue()
    {
        $this->assertTrue($this->object->isDateJustifiable());
        
        $mockReport = \Mockery::mock('AppBundle\Entity\Report')->shouldReceive([ 'getStartDate' => new \DateTime('2013-10-10'),
                                                                                   'getEndDate' => new \DateTime('2014-08-10')
                                                                                 ])->getMock();
        
        $this->object->setOpeningDate(new \DateTime('2013-10-10'));
        $this->object->setClosingDate(new \DateTime('2014-08-10'));
        $this->object->setReport($mockReport);
        
        $this->assertTrue($this->object->isDateJustifiable());
    }
    
    /**
     * Testing is date justifiable when false
     */
    public function testIsDateJustifiableFalse()
    {
        $this->object->setOpeningDate(new \DateTime('2013-10-11'));
        $this->object->setClosingDate(new \DateTime('2014-08-11'));
        
        $mockReport = \Mockery::mock('AppBundle\Entity\Report')->shouldReceive([ 'getStartDate' => new \DateTime('2013-10-10'),
                                                                                   'getEndDate' => new \DateTime('2014-08-10')
                                                                                 ])->getMock();
        
        $this->object->setReport($mockReport);
        
        $this->assertFalse($this->object->isDateJustifiable());
    }
    
    /**
     * Testing is balance justifiable when false
     */
    public function testIsBalanceJustifiableFalse()
    {
        $mockIncome = \Mockery::mock('AppBundle\Entity\Income')->shouldReceive('getTotal')->andReturn(100)->getMock();
        $mockBenefit = \Mockery::mock('AppBundle\Entity\Benefit')->shouldReceive('getTotal')->andReturn(100)->getMock();
        $mockExpenditure = \Mockery::mock('AppBundle\Entity\Expenditure')->shouldReceive('getTotal')->andReturn(10)->getMock();
        
        //add incomes
        $this->object->addIncome($mockIncome);
        $this->object->addIncome($mockIncome);
        $this->object->addIncome($mockIncome);
        
        //add benefits
        $this->object->addBenefit($mockBenefit);
        $this->object->addBenefit($mockBenefit);
        $this->object->addBenefit($mockBenefit);
        
        //add expenditure
        $this->object->addExpenditure($mockExpenditure);
        $this->object->addExpenditure($mockExpenditure);
        $this->object->addExpenditure($mockExpenditure);
        
        $this->object->setClosingBalance(0);
        $this->object->setOpeningBalance(0);
        
        $this->assertFalse($this->object->isBalanceJustifiable());
    }
    
    /**
     * Testing is balance justifiable when true
     */
    public function testIsBalanceJustifiableTrue()
    {
        $this->assertTrue($this->object->isBalanceJustifiable());
    }
    
    /**
     * Testing is get balance offset
     */
    public function testGetBalanceOffset()
    {
//        $mockIncome = \Mockery::mock('AppBundle\Entity\Income')->shouldReceive('getTotal')->andReturn(100)->getMock();
//        $mockBenefit = \Mockery::mock('AppBundle\Entity\Benefit')->shouldReceive('getTotal')->andReturn(100)->getMock();
//        $mockExpenditure = \Mockery::mock('AppBundle\Entity\Expenditure')->shouldReceive('getTotal')->andReturn(10)->getMock();
//        
//        $this->object->setClosingBalance(1000);
//        
//        $this->assertEquals(430,$this->object->getBalanceOffset());
    }
    
    /**
     * testing get current balance when zero
     */
    public function testGetCurrentBalanceWhenZero()
    {
        $this->assertEquals(0,$this->object->getCurrentBalance());
    }
    
    
    /**
     * testing get current balance when not zero
     */
    public function testGetCurrentBalanceWhenNotZero()
    {
        $mockIncome = \Mockery::mock('AppBundle\Entity\Income')->shouldReceive('getTotal')->andReturn(100)->getMock();
        $mockBenefit = \Mockery::mock('AppBundle\Entity\Benefit')->shouldReceive('getTotal')->andReturn(100)->getMock();
        $mockExpenditure = \Mockery::mock('AppBundle\Entity\Expenditure')->shouldReceive('getTotal')->andReturn(10)->getMock();
        
        //add incomes
        $this->object->addIncome($mockIncome);
        $this->object->addIncome($mockIncome);
        $this->object->addIncome($mockIncome);
        
        //add benefits
        $this->object->addBenefit($mockBenefit);
        $this->object->addBenefit($mockBenefit);
        $this->object->addBenefit($mockBenefit);
        
        //add expenditure
        $this->object->addExpenditure($mockExpenditure);
        $this->object->addExpenditure($mockExpenditure);
        $this->object->addExpenditure($mockExpenditure);
        
        $this->object->setOpeningBalance(50);
        
        $this->assertEquals(620,($this->object->getCurrentBalance()));
    }
}
