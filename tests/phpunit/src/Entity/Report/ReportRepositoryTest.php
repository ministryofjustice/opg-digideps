<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity as EntityDir;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportRepositoryTest extends WebTestCase
{
    /**
     * @var \Fixtures
     */
    private $fixtures;

    public function setUp()
    {
        $client = static::createClient(['environment' => 'test',
                                               'debug' => true, ]);
        $em = $client->getContainer()->get('em');

        $em->clear();

        $this->fixtures = new \Fixtures($em);
    }

    public function testcreateNextYearReport()
    {
        $user = $this->fixtures->createUser();
        $client = $this->fixtures->createClient($user);
        $report = $this->fixtures->createReport($client, [
            'setStartDate' => new \DateTime('01 January 2014'),
            'setEndDate' => new \DateTime('31 December 2014'),
        ]);

        $asset = new EntityDir\Report\AssetOther();
        $asset->setReport($report);
        $asset->setTitle('test');
        $asset->setDescription('test');
        $asset->setValue(100);
        $asset->setValuationDate(new \DateTime('10 June 2013'));

        $account = $this->fixtures->createAccount($report, [
            'setBank' => 'NATWEST',
            'setAccountType' => 'Current',
            'setSortCode' => '120044',
            'setAccountNumber' => '0012',
            'setCreatedAt' => new \DateTime(),
        ]);

        $this->fixtures->persist($asset);

        $this->fixtures->flush();
        $reportId = $report->getId();
        $this->fixtures->clear();

        // call method

        $report = $this->fixtures->getRepo('Report\Report')->find($reportId);
        $reportId = $this->fixtures->getRepo('Report\Report')->createNextYearReport($report)->getId();

        // re-clear fixtures
        $this->fixtures->clear();

        $newReport = $this->fixtures->getRepo('Report\Report')->find($reportId);

        $this->assertEquals($newReport->getStartDate()->format('Y-m-d'), '2015-01-01');
        $this->assertEquals($newReport->getEndDate()->format('Y-m-d'), '2015-12-31');
        $this->assertCount(1, $newReport->getAssets());

        $assert = $newReport->getAssets()[0];

        $this->assertEquals('test', $asset->getTitle());
        $this->assertEquals('test', $asset->getDescription());
        $this->assertEquals(100, $asset->getValue());
        $this->assertEquals('2013-06-10', $asset->getValuationDate()->format('Y-m-d'));

        $this->assertCount(1, $newReport->getAccounts());

        /** @var $account Account */
        $account = $newReport->getAccounts()[0];

        $this->assertEquals('NATWEST', $account->getBank());
        $this->assertEquals('Current', $account->getAccountType());
        $this->assertEquals('120044', $account->getSortCode());
        $this->assertEquals('0012', $account->getAccountNumber());
    }
}
