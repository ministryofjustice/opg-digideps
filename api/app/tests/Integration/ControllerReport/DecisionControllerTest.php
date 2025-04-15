<?php

namespace App\Tests\Integration\ControllerReport;

use App\Entity\Report\Report;
use App\Tests\Integration\Controller\AbstractTestController;

class DecisionControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $decision1;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $decision2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$decision1 = self::fixtures()->createDecision(self::$report1, ['setDescription' => 'description1']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);
        self::$decision2 = self::fixtures()->createDecision(self::$report2);

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
        'description' => 'description-changed',
        'client_involved_boolean' => true,
        'client_involved_details' => 'client_involved_details-changed',
    ];

    public function testgetOneByIdAuth()
    {
        $url = '/report/decision/'.self::$decision1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testgetOneByIdAcl()
    {
        $url2 = '/report/decision/'.self::$decision2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testgetOneById()
    {
        $url = '/report/decision/'.self::$decision1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$decision1->getId(), $data['id']);
        $this->assertEquals(self::$decision1->getDescription(), $data['description']);
    }

    public function testgetDecisions()
    {
        $data = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['decision']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data']['decisions'];

        $this->assertCount(1, $data);
        $this->assertEquals(self::$decision1->getId(), $data[0]['id']);
        $this->assertEquals(self::$decision1->getDescription(), $data[0]['description']);
    }

    public function testupsertDecisionAuth()
    {
        $url = '/report/decision';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    /**
     * @depends testgetDecisions
     */
    public function testupsertDecisionAcl()
    {
        $url2 = '/report/decision';

        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy, [
            'report_id' => self::$report2->getId(),
        ]);
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy, [
            'id' => self::$decision2->getId(),
        ]);
    }

    public function testupsertDecisionMissingParams()
    {
        $url = '/report/decision';

        // empty params
        $errorMessage = $this->assertJsonRequest('POST', $url, [
            'data' => [
                'report_id' => self::$report1->getId(),
            ],
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'assertResponseCode' => 400,
        ])['message'];
        $this->assertStringContainsString('description', $errorMessage);
        $this->assertStringContainsString('client_involved_boolean', $errorMessage);
        $this->assertStringContainsString('client_involved_details', $errorMessage);
    }

    public function testupsertDecisionPut()
    {
        $url = '/report/decision';

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['id' => self::$decision1->getId()] + $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $decision = self::fixtures()->getRepo('Report\Decision')->find($return['data']['id']); /* @var $decision \App\Entity\Report\Decision */
        $this->assertEquals('description-changed', $decision->getDescription());
        $this->assertEquals(self::$report1->getId(), $decision->getReport()->getId());

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_DECISIONS));
    }

    public function testupsertDecisionPost()
    {
        $url = '/report/decision';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['report_id' => self::$report1->getId()] + $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        $decision = self::fixtures()->getRepo('Report\Decision')->find($return['data']['id']); /* @var $decision \App\Entity\Report\Decision */
        $this->assertEquals('description-changed', $decision->getDescription());
        $this->assertEquals(self::$report1->getId(), $decision->getReport()->getId());
        // TODO assert other fields
    }

    public function testDeleteDecisionAuth()
    {
        $url = '/report/decision/'.self::$decision1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteDecisionAcl()
    {
        $url2 = '/report/decision/'.self::$decision2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
    }

    /**
     * Run this last to avoid corrupting the data.
     *
     * @depends testgetDecisions
     */
    public function testDeleteDecision()
    {
        $url = '/report/decision/'.self::$decision1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(null === self::fixtures()->getRepo('Report\Decision')->find(self::$decision1->getId()));
    }
}
