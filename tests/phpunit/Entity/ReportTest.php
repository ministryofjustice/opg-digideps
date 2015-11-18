<?php
namespace AppBundle\Entity;

use Mockery as m;

class ReportTest extends \PHPUnit_Framework_TestCase
{



    public function testTotals()
    {
        $this->markTestIncomplete('use new logic');

        $report = new Account();
        $report->getAccounts()
        $report->setOpeningBalance(10.0);

        $this->assertTrue(time() - $report->getCreatedAt()->getTimestamp() < 1000, 'account.createdAt not set with current date');

        // add account transaction type
        $in1 = new TransactionTypeIn();
        $in1->setId('in1')->setHasMoreDetails(true);

        $in2 = new TransactionTypeIn();
        $in2->setId('in2')->setHasMoreDetails(false);

        //add account transaction type
        $out1 = new TransactionTypeOut();
        $out1->setId('out1')->setHasMoreDetails(true);

        $out2 = new TransactionTypeOut();
        $out2->setId('out2')->setHasMoreDetails(false);

        // add account transaction
        $report->addTransaction(new Transaction($report, $in1, 400.0));
        $report->addTransaction(new Transaction($report, $in2, 150.0));

        // add account transaction
        $report->addTransaction(new Transaction($report, $out1, 50.0));
        $report->addTransaction(new Transaction($report, $out2, 30.0));

        $this->assertEquals(400.0 + 150.0, $report->getMoneyInTotal());
        $this->assertEquals(50.0 + 30.0, $report->getMoneyOutTotal());
        $this->assertEquals(10.0 + 400.0 + 150.0 - 50.0 - 30.0, $report->getMoneyTotal());

        // edit transaction
        $report->findTransactionByTypeId('in1')->setAmount(400.50); //edit (1)
        $this->assertEquals(10.0 + 400.50 + 150.0 - 50.0 - 30.0, $report->getMoneyTotal());

    }
}
