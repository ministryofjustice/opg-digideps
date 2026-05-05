<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\Action;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class ActionControllerTest extends AbstractTestController
{
    private static $user1;
    private static $report1;
    private static $user2;
    private static $report2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        // deputy1
        self::$user1 = self::fixtures()->getRepo(User::class)->findOneByEmail('deputy@example.org');
        self::$report1 = self::fixtures()->setupReportForDeputyUser(self::$user1, clientSetters: ['setFirstname' => 'c1']);

        // deputy 2
        self::$user2 = self::fixtures()->createUser();
        self::$report2 = self::fixtures()->setupReportForDeputyUser(self::$user2);

        self::fixtures()->flush()->clear();

        if (self::$tokenAdmin === null) {
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

    public function testUpdateAuth(): void
    {
        $url = '/report/' . self::$report1->getId() . '/action';
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testUpdateAcl(): void
    {
        $url2 = '/report/' . self::$report2->getId() . '/action';

        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    public function testUpdate(): void
    {
        $url = '/report/' . self::$report1->getId() . '/action';

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

        $action = self::fixtures()->getRepo(Action::class)->find($return['data']['id']);
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
        $action = self::fixtures()->getRepo(Action::class)->find($return['data']['id']); /* @var $action Action */
        $this->assertEquals('no', $action->getDoYouExpectFinancialDecisions());
        $this->assertEquals(null, $action->getDoYouExpectFinancialDecisionsDetails());
        $this->assertEquals('no', $action->getDoYouHaveConcerns());
        $this->assertEquals(null, $action->getDoYouHaveConcernsDetails());

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_ACTIONS));
    }
}
