<?php

namespace AppBundle\Entity;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    public function testSetterGetters()
    {
        $account = new Account();
        $account->setLastEdit(new \DateTime('2015-01-01'));
        $this->assertEquals('123456', $account->setAccountNumber('123456')->getAccountNumber());
        $this->assertEquals('123456', $account->setBank('123456')->getBank());
        $this->assertEquals('123456', $account->setClosingBalance('123456')->getClosingBalance());
        $this->assertEquals('123456', $account->setOpeningBalance('123456')->getOpeningBalance());
    }
}
