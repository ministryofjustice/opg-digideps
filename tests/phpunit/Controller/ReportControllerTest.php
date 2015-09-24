<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;

class ReportControllerTest extends AbstractTestController
{
    public function testCloneAction()
    {
        $token = $this->login('deputy@example.org');
        
        $client = $this->fixtures->createClient();
        $report = $this->fixtures->createReport($client, [
            'setStartDate' => new \DateTime('01 January 2014'),
            'setEndDate' => new \DateTime('31 December 2014')
        ]);
        
        $asset = new EntityDir\Asset();
        $asset->setReport($report);
        $asset->setTitle('test');
        $asset->setDescription('test');
        $asset->setValue(100);
        $asset->setValuationDate(new \DateTime('10 June 2013'));
        
        $account = new EntityDir\Account();
        $account->setOpeningDate(new \DateTime('01 January 2014'))
            ->setClosingDate(new \DateTime('31 December 2014'))
            ->setReport($report)
            ->setBank('NATWEST')
            ->setSortCode('120044')
            ->setAccountNumber('0012')
            ->setCreatedAt(new \DateTime());

        $this->fixtures->getRepo('Account')->addEmptyTransactionsToAccount($account);

        $this->fixtures->persist($asset, $account);
        $this->fixtures->flush();
        $this->fixtures->clear();

        $responseArray = $this->assertRequest('POST', '/report/clone', [
            'mustSucceed'=>true,
            'data'=> [
                'id' => $report->getId()
            ],
            'AuthToken' => $token,
        ]);
        
        $reportId = $responseArray['data']['report'];
    
        $this->fixtures->clear();

        $newReport = $this->fixtures->getRepo('Report')->find($reportId);
    
        $this->assertEquals($newReport->getStartDate()->format('Y-m-d'),'2015-01-01');
        $this->assertEquals($newReport->getEndDate()->format('Y-m-d'), '2015-12-31');
        $this->assertCount(1, $newReport->getAssets());

        $assert = $newReport->getAssets()[0];

        $this->assertEquals('test', $asset->getTitle());
        $this->assertEquals('test', $asset->getDescription());
        $this->assertEquals(100, $asset->getValue());
        $this->assertEquals('2013-06-10', $asset->getValuationDate()->format('Y-m-d'));

        $this->assertCount(1, $newReport->getAccounts());

        $account = $newReport->getAccounts()[0];

        $this->assertEquals('2014-12-31', $account->getOpeningDate()->format('Y-m-d'));
        $this->assertEquals('NATWEST', $account->getBank());
        $this->assertEquals('120044', $account->getSortCode());
        $this->assertEquals('0012', $account->getAccountNumber());
        $this->assertCount(40,$account->getTransactions());
    }
}

