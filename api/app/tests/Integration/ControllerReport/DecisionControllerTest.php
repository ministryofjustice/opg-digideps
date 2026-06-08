<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\Decision;
use OPG\Digideps\Backend\Entity\Report\Report;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class DecisionControllerTest extends AbstractTestController
{
    private static Report $report1;
    private static Decision $decision1;
    private static Report $report2;
    private static Decision $decision2;
    private static string $tokenAdmin;
    private static string $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        ['persons' => ['users' => ['lay1' => $user1]], 'orders' => [['pfa' => ['reports' => [self::$report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        ['orders' => [['pfa' => ['reports' => [self::$report2]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        self::$decision1 = self::fixtures()->createDecision(self::$report1, ['setDescription' => 'description1']);
        self::$decision2 = self::fixtures()->createDecision(self::$report2);

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
        'description' => 'description-changed',
        'client_involved_boolean' => true,
        'client_involved_details' => 'client_involved_details-changed',
    ];

    public function testGetOneByIdAuth(): void
    {
        $url = '/report/decision/' . self::$decision1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testGetOneByIdAcl(): void
    {
        $url2 = '/report/decision/' . self::$decision2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testGetOneById(): void
    {
        $url = '/report/decision/' . self::$decision1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$decision1->getId(), $data['id']);
        $this->assertEquals(self::$decision1->getDescription(), $data['description']);
    }

    public function testGetDecisions(): void
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

    public function testUpsertDecisionAuth(): void
    {
        $url = '/report/decision';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    /**
     * @depends testGetDecisions
     */
    public function testUpsertDecisionAcl(): void
    {
        $url2 = '/report/decision';

        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy, [
            'report_id' => self::$report2->getId(),
        ]);
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy, [
            'id' => self::$decision2->getId(),
        ]);
    }

    public function testUpsertDecisionMissingParams(): void
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

    public function testUpsertDecisionPut(): void
    {
        $url = '/report/decision';

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['id' => self::$decision1->getId()] + self::$dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $decision = self::fixtures()->getRepo(Decision::class)->find($return['data']['id']); /* @var $decision Decision */
        $this->assertEquals('description-changed', $decision->getDescription());
        $this->assertEquals(self::$report1->getId(), $decision->getReport()->getId());

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_DECISIONS));
    }

    public function testUpsertDecisionPost(): void
    {
        $url = '/report/decision';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['report_id' => self::$report1->getId()] + self::$dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        $decision = self::fixtures()->getRepo(Decision::class)->find($return['data']['id']); /* @var $decision Decision */
        $this->assertEquals('description-changed', $decision->getDescription());
        $this->assertEquals(self::$report1->getId(), $decision->getReport()->getId());
        // TODO assert other fields
    }

    public function testDeleteDecisionAuth(): void
    {
        $url = '/report/decision/' . self::$decision1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteDecisionAcl(): void
    {
        $url2 = '/report/decision/' . self::$decision2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
    }

    /**
     * Run this last to avoid corrupting the data.
     *
     * @depends testGetDecisions
     */
    public function testDeleteDecision(): void
    {
        $url = '/report/decision/' . self::$decision1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(self::fixtures()->getRepo(Decision::class)->find(self::$decision1->getId()) === null);
    }
}
