<?php

namespace App\Tests\Unit\ControllerReport;

use App\Entity\Report\Report;
use App\Tests\Unit\Controller\AbstractTestController;

class VisitsCareControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $visitsCare1;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $visitsCare2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        self::$fixtures::deleteReportsData(['safeguarding']);

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$visitsCare1 = self::fixtures()->createVisitsCare(self::$report1, ['setDoYouLiveWithClient' => 'y']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);
        self::$visitsCare2 = self::fixtures()->createVisitsCare(self::$report2);

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

    private $dataUpdate = [
        'do_you_live_with_client' => 'y-m',
        'how_often_do_you_visit' => 'ho-m',
        'how_often_do_you_contact_client' => 'hodycc',
    ];

    public function testgetOneByIdAuth()
    {
        $url = '/report/visits-care/'.self::$visitsCare1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    /**
     * @depends testgetOneByIdAuth
     */
    public function testgetOneByIdAcl()
    {
        $url2 = '/report/visits-care/'.self::$visitsCare2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testgetOneByIdAcl
     */
    public function testgetOneById()
    {
        $url = '/report/visits-care/'.self::$visitsCare1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$visitsCare1->getId(), $data['id']);
        $this->assertEquals(self::$visitsCare1->getDoYouLiveWithClient(), $data['do_you_live_with_client']);
    }

    /**
     * @depends testgetOneById
     */
    public function testgetVisitsCaresAuth()
    {
        $url = '/report/'.self::$report1->getId().'/visits-care';

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    /**
     * @depends testgetVisitsCaresAuth
     */
    public function testgetVisitsCaresAcl()
    {
        $url2 = '/report/'.self::$report2->getId().'/visits-care';

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testgetVisitsCaresAcl
     */
    public function testgetVisitsCares()
    {
        $url = '/report/'.self::$report1->getId().'/visits-care';

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
     * @depends testgetVisitsCares
     */
    public function testAddUpdateAuth()
    {
        $url = '/report/visits-care';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
    }

    /**
     * @depends testAddUpdateAuth
     */
    public function testAddUpdateAcl()
    {
        $url2post = '/report/visits-care';
        $url2put = '/report/visits-care/'.self::$visitsCare2->getId();

        $this->assertEndpointNotAllowedFor('POST', $url2post, self::$tokenDeputy, [
            'report_id' => ['id' => self::$report2->getId()],
        ]);
        $this->assertEndpointNotAllowedFor('PUT', $url2put, self::$tokenDeputy);
    }

    /**
     * @depends testAddUpdateAcl
     */
    public function testUpdate()
    {
        $url = '/report/visits-care/'.self::$visitsCare1->getId();

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_VISITS_CARE));

        $visitsCare = self::fixtures()->getRepo('Report\VisitsCare')->find($return['data']['id']); /* @var $visitsCare \App\Entity\Report\VisitsCare */
        $this->assertEquals('y-m', $visitsCare->getDoYouLiveWithClient());
        $this->assertEquals('hodycc', $visitsCare->getHowOftenDoYouContactClient());
        $this->assertEquals(self::$report1->getId(), $visitsCare->getReport()->getId());
        // TODO assert other fields
    }

    /**
     * @depends testAddUpdateAcl
     */
    public function testDeleteVisitsCareAuth()
    {
        $url = '/report/visits-care/'.self::$visitsCare1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    /**
     * @depends testDeleteVisitsCareAuth
     */
    public function testDeleteVisitsCareAcl()
    {
        $url2 = '/report/visits-care/'.self::$visitsCare2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
    }

    /**
     * Run this last to avoid corrupting the data.
     *
     * @depends testDeleteVisitsCareAcl
     */
    public function testDeleteVisitsCare()
    {
        $id = self::$visitsCare1->getId();
        $url = '/report/visits-care/'.$id;

        $data = $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(null === self::fixtures()->clear()->getRepo('Report\VisitsCare')->find($id));
    }

    /**
     * need the record to be deleted first.
     *
     * @depends testDeleteVisitsCare
     */
    public function testAdd()
    {
        $id = self::$visitsCare1->getId();
        $url = '/report/visits-care/'.$id;

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $url = '/report/visits-care';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['report_id' => self::$report1->getId()] + $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_VISITS_CARE));

        // assert account created with transactions
        $visitsCare = self::fixtures()->getRepo('Report\VisitsCare')->find($return['data']['id']); /* @var $visitsCare \App\Entity\Report\VisitsCare */
        $this->assertEquals('y-m', $visitsCare->getDoYouLiveWithClient());
        $this->assertEquals(self::$report1->getId(), $visitsCare->getReport()->getId());
    }
}
