<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\Gift;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class GiftControllerTest extends AbstractTestController
{
    private static Report $report1;
    private static Gift $gift1;
    private static Gift $gift2;
    private static Report $report2;
    private static string $tokenAdmin;
    private static string $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        ['persons' => ['users' => ['lay1' => $user1]], 'orders' => [['pfa' => ['reports' => [self::$report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        ['orders' => [['pfa' => ['reports' => [self::$report2]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        self::$gift1 = new Gift(self::$report1)->setExplanation('e1')->setAmount('1.10');
        self::$gift2 = new Gift(self::$report2)->setExplanation('e2')->setAmount('2.20');

        self::fixtures()->persist(self::$gift1, self::$gift2);
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

    public function testGetOneByIdAuth(): void
    {
        $url = '/report/' . self::$report1->getId() . '/gift/' . self::$gift1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testGetOneByIdAcl(): void
    {
        $url2 = '/report/' . self::$report1->getId() . '/gift/' . self::$gift2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testGetOneById(): void
    {
        $url = '/report/' . self::$report1->getId() . '/gift/' . self::$gift1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$gift1->getId(), $data['id']);
        $this->assertEquals(self::$gift1->getExplanation(), $data['explanation']);
        $this->assertEquals(self::$gift1->getAmount(), $data['amount']);
    }

    public function testPostPutAuth(): void
    {
        $url = '/report/' . self::$report1->getId() . '/gift';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);

        $url = '/report/' . self::$report1->getId() . '/gift/' . self::$gift1->getId();
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testPostPutAcl(): void
    {
        $url2 = '/report/' . self::$report2->getId() . '/gift';
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        $url2 = '/report/' . self::$report2->getId() . '/gift/' . self::$gift1->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $url3 = '/report/' . self::$report2->getId() . '/gift/' . self::$gift2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url3, self::$tokenDeputy);
    }

    public function testPostPutGetAll(): void
    {
        // POST
        $url = '/report/' . self::$report1->getId() . '/gift';
        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'amount' => 3.3,
                'explanation' => 'e3',
            ],
        ]);
        $giftId = $return['data']['id'];
        $this->assertTrue($giftId > 0);

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_GIFTS));

        $gift = self::fixtures()->getRepo(Gift::class)->find($giftId);
        /* @var $gift Gift */
        $this->assertEquals(3.3, $gift->getAmount());
        $this->assertEquals('e3', $gift->getExplanation());

        // UPDATE
        $url = '/report/' . self::$report1->getId() . '/gift/' . $giftId;
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'amount' => 3.31,
                'explanation' => 'e3.1',
            ],
        ]);
        self::fixtures()->clear();

        $gift = self::fixtures()->getRepo(Gift::class)->find($giftId);
        /* @var $gift Gift */
        $this->assertEquals(3.31, $gift->getAmount());
        $this->assertEquals('e3.1', $gift->getExplanation());

        // GET ALL
        $url = '/report/' . self::$report1->getId();
        $q = http_build_query(['groups' => ['gifts']]);
        // assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertCount(2, $data['gifts']);
        $this->assertTrue($data['gifts'][0]['id'] > 0);
        $this->assertEquals('e1', $data['gifts'][0]['explanation']);
        $this->assertEquals(1.1, $data['gifts'][0]['amount']);
        $this->assertTrue($data['gifts'][1]['id'] > 0);
        $this->assertEquals('e3.1', $data['gifts'][1]['explanation']);
        $this->assertEquals(3.31, $data['gifts'][1]['amount']);
    }

    public function testDeleteAuth(): void
    {
        $url = '/report/' . self::$report1->getId() . '/gift/' . self::$gift1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteAcl(): void
    {
        $url2 = '/report/' . self::$report1->getId() . '/gift/' . self::$gift2->getId();
        $url3 = '/report/' . self::$report2->getId() . '/gift/' . self::$gift2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
        $this->assertEndpointNotAllowedFor('DELETE', $url3, self::$tokenDeputy);
    }

    /**
     * @depends testPostPutGetAll
     */
    public function testDelete(): void
    {
        $url = '/report/' . self::$report1->getId() . '/gift/' . self::$gift1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(self::fixtures()->getRepo(Gift::class)->find(self::$gift1->getId()) === null);
    }

    /**
     * @depends testDelete
     */
    public function testGiftsExist(): void
    {
        $report = self::fixtures()->getReportById(self::$report1->getId());
        $this->assertNotNull($report);
        $report->setGiftsExist('yes');

        self::fixtures()->persist($report);
        self::fixtures()->flush();

        $this->assertCount(1, $report->getGifts());
        $this->assertEquals('yes', $report->getGiftsExist());

        $url = '/report/' . self::$report1->getId();
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'gifts_exist' => 'no',
            ],
        ]);

        self::fixtures()->clear();
        $report = self::fixtures()->getReportById(self::$report1->getId());
        $this->assertEquals('no', $report->getGiftsExist());
        $this->assertCount(0, $report->getGifts());
    }
}
