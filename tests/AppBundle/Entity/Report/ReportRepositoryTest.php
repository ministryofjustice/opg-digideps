<?php

namespace Tests\AppBundle\Entity\Report;

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report\Report;
use Mockery as m;

class ReportRepositoryTest extends WebTestCase
{
    /**
     * @var \Fixtures
     */
    private static $fixtures;
    private static $repo;

    public static function setUpBeforeClass()
    {
        $client = static::createClient(['environment' => 'test',
                                               'debug' => true, ]);

        $em = $client->getContainer()->get('em');
        self::$fixtures = new \Fixtures($em);
        self::$fixtures->deleteReportsData();

        $em->clear();

        self::$repo = self::$fixtures->getRepo('Report\Report'); /** @var self::$repo ReportRepository */

    }

    /**
     * @test
     */
    public function createNextYearReportCopiesData()
    {
        $user = self::$fixtures->createUser();
        $client = self::$fixtures->createClient($user);
        $report = self::$fixtures->createReport($client, [
            'setStartDate' => new \DateTime('01 January 2014'),
            'setEndDate' => new \DateTime('31 December 2014'),
        ]);

        // assets
        $asset = new EntityDir\Report\AssetOther();
        $asset->setReport($report);
        $asset->setTitle('test');
        $asset->setDescription('test');
        $asset->setValue(100);
        $asset->setValuationDate(new \DateTime('10 June 2013'));

        // account
        $account = self::$fixtures->createAccount($report, [
            'setBank' => 'NATWEST',
            'setAccountType' => 'Current',
            'setSortCode' => '120044',
            'setAccountNumber' => '0012',
            'setCreatedAt' => new \DateTime(),
        ]);
        self::$fixtures->persist($asset);

        // flush and clear
        self::$fixtures->flush();
        $reportId = $report->getId();
        self::$fixtures->clear();

        // call method
        $report = self::$repo->find($reportId);
        $reportId = self::$repo->createNextYearReport($report)->getId();

        // get fresh report
        self::$fixtures->clear();
        $newReport = self::$repo->find($reportId);

        // check report properties
        $this->assertEquals($newReport->getStartDate()->format('Y-m-d'), '2015-01-01');
        $this->assertEquals($newReport->getEndDate()->format('Y-m-d'), '2015-12-31');
        $this->assertCount(1, $newReport->getAssets());

        // check assets
        $asset = $newReport->getAssets()[0];
        $this->assertEquals('test', $asset->getTitle());
        $this->assertEquals('test', $asset->getDescription());
        $this->assertEquals(100, $asset->getValue());
        $this->assertEquals('2013-06-10', $asset->getValuationDate()->format('Y-m-d'));

        // check bank accounts
        $this->assertCount(1, $newReport->getBankAccounts());
        $account = $newReport->getBankAccounts()[0];  /** @var $account Account */
        $this->assertEquals('NATWEST', $account->getBank());
        $this->assertEquals('Current', $account->getAccountType());
        $this->assertEquals('120044', $account->getSortCode());
        $this->assertEquals('0012', $account->getAccountNumber());
    }

    public static function createNextYearReportChangesTypeProvider()
    {
        return [
            // under 21k, 103 gets created
            [0, Report::TYPE_102, Report::TYPE_103],
            [0, Report::TYPE_103, Report::TYPE_103],
            [1, Report::TYPE_102, Report::TYPE_103],
            [1, Report::TYPE_103, Report::TYPE_103],
            [21000, Report::TYPE_102, Report::TYPE_103],
            [21000, Report::TYPE_103, Report::TYPE_103],
            // over 21k, reports turn 102
            [21001, Report::TYPE_102, Report::TYPE_102],
            [21001, Report::TYPE_103, Report::TYPE_102],
            [1000000, Report::TYPE_102, Report::TYPE_102],
            [1000000, Report::TYPE_103, Report::TYPE_102],
            // 104 should never change
            [1, Report::TYPE_104, Report::TYPE_104],
            [1000000, Report::TYPE_104, Report::TYPE_104],
        ];
    }

    /**
     * //TODO avoid setUp being called for this method
     * @test
     * @dataProvider createNextYearReportChangesTypeProvider
     */
    public function createNextYearReportChangesType($reportAssetTotalValue, $initialType, $newReportType)
    {
        $report = m::mock(Report::class, [
            'getType' => $initialType,
            'getEndDate' => new \DateTime(),
            'getStartDate' => new \DateTime(),
            'getAssets' => [],
            'getBankAccounts' => [],
            'getAssetsTotalValue' => $reportAssetTotalValue,
        ]);
        $report->shouldIgnoreMissing();
        $em = m::mock(EntityManager::class);
        $em->shouldIgnoreMissing();

        $cm = m::mock(ClassMetadata::class);
        $repo = new EntityDir\Report\ReportRepository($em, $cm);

        $newReport = $repo->createNextYearReport($report);
        $this->assertEquals($newReportType, $newReport->getType());
    }
}
