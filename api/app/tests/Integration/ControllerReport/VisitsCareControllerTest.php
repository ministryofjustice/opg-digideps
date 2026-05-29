<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Report\VisitsCare;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class VisitsCareControllerTest extends AbstractTestController
{
    private static Report $report1;
    private static VisitsCare $visitsCare1;
    private static Report $report2;
    private static VisitsCare $visitsCare2;
    private static string $tokenAdmin = '';
    private static string $tokenDeputy = '';

    public function setUp(): void
    {
        parent::setUp();

        self::$fixtures::deleteReportsData(['safeguarding']);

        ['persons' => ['users' => ['lay1' => $user1]], 'orders' => [['pfa' => ['reports' => [self::$report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        ['orders' => [['pfa' => ['reports' => [self::$report2]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        self::$visitsCare1 = self::fixtures()->createVisitsCare(self::$report1, ['setDoYouLiveWithClient' => 'y']);
        self::$visitsCare2 = self::fixtures()->createVisitsCare(self::$report2);

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

    private static array $dataUpdate = [
        'do_you_live_with_client' => 'y-m',
        'how_often_do_you_visit' => 'ho-m',
        'how_often_do_you_contact_client' => 'hodycc',
    ];

    public function testGetOneByIdAuth(): void
    {
        $url = '/report/visits-care/' . self::$visitsCare1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    /**
     * @depends testGetOneByIdAuth
     */
    public function testGetOneByIdAcl(): void
    {
        $url2 = '/report/visits-care/' . self::$visitsCare2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testGetOneByIdAcl
     */
    public function testGetOneById(): void
    {
        $url = '/report/visits-care/' . self::$visitsCare1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$visitsCare1->getId(), $data['id']);
        $this->assertEquals(self::$visitsCare1->getDoYouLiveWithClient(), $data['do_you_live_with_client']);
    }

    /**
     * @depends testGetOneById
     */
    public function testGetVisitsCaresAuth(): void
    {
        $url = '/report/' . self::$report1->getId() . '/visits-care';

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    /**
     * @depends testGetVisitsCaresAuth
     */
    public function testGetVisitsCaresAcl(): void
    {
        $url2 = '/report/' . self::$report2->getId() . '/visits-care';

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testGetVisitsCaresAcl
     */
    public function testGetVisitsCares(): void
    {
        $url = '/report/' . self::$report1->getId() . '/visits-care';

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertCount(1, $data);
        $this->assertEquals(self::$visitsCare1->getId(), $data[0]['id']);
        $this->assertEquals(self::$visitsCare1->getDoYouLiveWithClient(), $data[0]['do_you_live_with_client']);
    }

    /**
     * @depends testGetVisitsCares
     */
    public function testAddUpdateAuth(): void
    {
        $url = '/report/visits-care';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
    }

    /**
     * @depends testAddUpdateAuth
     */
    public function testAddUpdateAcl(): void
    {
        $url2post = '/report/visits-care';
        $url2put = '/report/visits-care/' . self::$visitsCare2->getId();

        $this->assertEndpointNotAllowedFor('POST', $url2post, self::$tokenDeputy, [
            'report_id' => ['id' => self::$report2->getId()],
        ]);
        $this->assertEndpointNotAllowedFor('PUT', $url2put, self::$tokenDeputy);
    }

    /**
     * @depends testAddUpdateAcl
     */
    public function testUpdate(): void
    {
        $url = '/report/visits-care/' . self::$visitsCare1->getId();

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => self::$dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_VISITS_CARE));

        $visitsCare = self::fixtures()->getRepo(VisitsCare::class)->find($return['data']['id']);
        $this->assertEquals('y-m', $visitsCare->getDoYouLiveWithClient());
        $this->assertEquals('hodycc', $visitsCare->getHowOftenDoYouContactClient());
        $this->assertEquals(self::$report1->getId(), $visitsCare->getReport()->getId());
        // TODO assert other fields
    }

    /**
     * @depends testAddUpdateAcl
     */
    public function testDeleteVisitsCareAuth(): void
    {
        $url = '/report/visits-care/' . self::$visitsCare1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    /**
     * @depends testDeleteVisitsCareAuth
     */
    public function testDeleteVisitsCareAcl(): void
    {
        $url2 = '/report/visits-care/' . self::$visitsCare2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
    }

    /**
     * Run this last to avoid corrupting the data.
     *
     * @depends testDeleteVisitsCareAcl
     */
    public function testDeleteVisitsCare(): void
    {
        $id = self::$visitsCare1->getId();
        $url = '/report/visits-care/' . $id;

        $data = $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(self::fixtures()->clear()->getRepo(VisitsCare::class)->find($id) === null);
    }

    /**
     * need the record to be deleted first.
     *
     * @depends testDeleteVisitsCare
     */
    public function testAdd(): void
    {
        $id = self::$visitsCare1->getId();
        $url = '/report/visits-care/' . $id;

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $url = '/report/visits-care';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['report_id' => self::$report1->getId()] + self::$dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_VISITS_CARE));

        // assert account created with transactions
        $visitsCare = self::fixtures()->getRepo(VisitsCare::class)->find($return['data']['id']); /* @var $visitsCare VisitsCare */
        $this->assertEquals('y-m', $visitsCare->getDoYouLiveWithClient());
        $this->assertEquals(self::$report1->getId(), $visitsCare->getReport()->getId());
    }
}
