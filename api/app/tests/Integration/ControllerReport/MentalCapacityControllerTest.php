<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\Gift;
use OPG\Digideps\Backend\Entity\Report\MentalCapacity;
use OPG\Digideps\Backend\Entity\Report\Report;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class MentalCapacityControllerTest extends AbstractTestController
{
    private static Report $report1;
    private static Report $report2;
    private static string $tokenAdmin;
    private static string $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        ['persons' => ['users' => ['lay1' => $user1]], 'orders' => [['pfa' => ['reports' => [self::$report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        ['orders' => [['pfa' => ['reports' => [self::$report2]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        self::fixtures()->flush()->clear();

        self::$tokenAdmin = $this->loginAsAdmin();
        self::$tokenDeputy = $this->loginAsDeputy($user1->getEmail());
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
        $url = '/report/' . self::$report1->getId() . '/mental-capacity';
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testUpdateAcl(): void
    {
        $url2 = '/report/' . self::$report2->getId() . '/mental-capacity';

        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    public function testUpdate(): void
    {
        $url = '/report/' . self::$report1->getId() . '/mental-capacity';

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

        $mc = self::fixtures()->getRepo(MentalCapacity::class)->find($return['data']['id']); /* @var $mc MentalCapacity */
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
        $mc = self::fixtures()->getRepo(MentalCapacity::class)->find($return['data']['id']); /* @var $mc MentalCapacity */
        $this->assertEquals(MentalCapacity::CAPACITY_STAYED_SAME, $mc->getHasCapacityChanged());
        $this->assertEquals(null, $mc->getHasCapacityChangedDetails());
        $this->assertEquals('2016-01-01', $mc->getMentalAssessmentDate()->format('Y-m-d'));
    }
}
