<?php

namespace App\Tests\Unit\ControllerReport;

use App\Entity\Report\MentalCapacity;
use App\Entity\Report\Report;
use app\tests\Integration\Controller\AbstractTestController;

class MentalCapacityControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$report1 = self::fixtures()->createReport(self::$client1);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);

        self::fixtures()->flush()->clear();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testupdateAuth()
    {
        $url = '/report/'.self::$report1->getId().'/mental-capacity';
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testupdateAcl()
    {
        $url2 = '/report/'.self::$report2->getId().'/mental-capacity';

        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    public function testupdate()
    {
        $url = '/report/'.self::$report1->getId().'/mental-capacity';

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'has_capacity_changed' => MentalCapacity::CAPACITY_CHANGED,
                'has_capacity_changed_details' => 'ccd',
                'mental_assessment_date' => '2015-12-31',
            ],
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_DECISIONS));

        $mc = self::fixtures()->getRepo('Report\MentalCapacity')->find($return['data']['id']); /* @var $mc \App\Entity\Report\MentalCapacity */
        $this->assertEquals(MentalCapacity::CAPACITY_CHANGED, $mc->getHasCapacityChanged());
        $this->assertEquals('ccd', $mc->getHasCapacityChangedDetails());
        $this->assertEquals('2015-12-31', $mc->getMentalAssessmentDate()->format('Y-m-d'));

        // update with choice not requiring details. (covers record existing and also data cleaned up ok)
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'has_capacity_changed' => MentalCapacity::CAPACITY_STAYED_SAME,
                'has_capacity_changed_details' => 'should no tbe saved',
                'mental_assessment_date' => '2016-01-01',
            ],
        ]);
        $this->assertTrue($return['data']['id'] > 0);
        self::fixtures()->clear();
        $mc = self::fixtures()->getRepo('Report\MentalCapacity')->find($return['data']['id']); /* @var $mc \App\Entity\Report\MentalCapacity */
        $this->assertEquals(MentalCapacity::CAPACITY_STAYED_SAME, $mc->getHasCapacityChanged());
        $this->assertEquals(null, $mc->getHasCapacityChangedDetails());
        $this->assertEquals('2016-01-01', $mc->getMentalAssessmentDate()->format('Y-m-d'));
    }
}
