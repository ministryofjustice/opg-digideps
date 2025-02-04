<?php

namespace App\Tests\Unit\ControllerReport;

use App\Entity\Report\Report;
use App\Tests\Unit\Controller\AbstractTestController;

class ActionControllerTest extends AbstractTestController
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
        $url = '/report/'.self::$report1->getId().'/action';
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testupdateAcl()
    {
        $url2 = '/report/'.self::$report2->getId().'/action';

        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    public function testupdate()
    {
        $url = '/report/'.self::$report1->getId().'/action';

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'do_you_expect_financial_decisions' => 'yes',
                'do_you_expect_financial_decisions_details' => 'fdd',
                'do_you_have_concerns' => 'yes',
                'do_you_have_concerns_details' => 'cd',
            ],
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $action = self::fixtures()->getRepo('Report\Action')->find($return['data']['id']); /* @var $action \App\Entity\Report\Action */
        $this->assertEquals('yes', $action->getDoYouExpectFinancialDecisions());
        $this->assertEquals('fdd', $action->getDoYouExpectFinancialDecisionsDetails());
        $this->assertEquals('yes', $action->getDoYouHaveConcerns());
        $this->assertEquals('cd', $action->getDoYouHaveConcernsDetails());

        // update with choice not requiring details. (covers record existing and also data cleaned up ok)
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'do_you_expect_financial_decisions' => 'no',
                'do_you_have_concerns' => 'no',
            ],
        ]);
        $this->assertTrue($return['data']['id'] > 0);
        self::fixtures()->clear();
        $action = self::fixtures()->getRepo('Report\Action')->find($return['data']['id']); /* @var $action \App\Entity\Report\Action */
        $this->assertEquals('no', $action->getDoYouExpectFinancialDecisions());
        $this->assertEquals(null, $action->getDoYouExpectFinancialDecisionsDetails());
        $this->assertEquals('no', $action->getDoYouHaveConcerns());
        $this->assertEquals(null, $action->getDoYouHaveConcernsDetails());

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_ACTIONS));
    }
}
