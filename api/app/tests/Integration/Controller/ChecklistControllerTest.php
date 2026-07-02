<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Entity\Report\Checklist;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Fixture\Scenario;

class ChecklistControllerTest extends AbstractTestController
{
    private static Report $report;
    private static Checklist $checklist;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        ['orders' => [['pfa' => ['reports' => [self::$report]]]]] = self::fixtureService()->instantiateScenario(Scenario::newSimpleLayScenario());

        self::$checklist = self::fixtureService()->persist(new Checklist(self::$report));
        self::fixtureService()->flush();
    }

    /**
     * @test
     */
    public function updateUsesSecretBasedAuth(): void
    {
        $return = $this->assertJsonRequest('PUT', '/checklist/32', [
            'mustFail' => true,
            'ClientSecret' => 'WRONG CLIENT SECRET',
            'assertCode' => 403,
            'assertResponseCode' => 403,
            'data' => [],
        ]);

        $this->assertStringContainsString('client secret not accepted', $return['message']);
    }

    /**
     * @test
     */
    public function updateUpdatesSyncStatusOnSuccess(): void
    {
        $url = sprintf('/checklist/%s', self::$checklist->getId());
        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => self::$deputySecret,
            'data' => ['syncStatus' => Checklist::SYNC_STATUS_SUCCESS],
        ]);

        self::assertEquals(self::$checklist->getId(), $response['data']['id']);
        self::assertEquals(Checklist::SYNC_STATUS_SUCCESS, $response['data']['synchronisation_status']);
        self::assertEqualsWithDelta(new \DateTime()->getTimestamp(), new \DateTime($response['data']['synchronisation_time'])->getTimestamp(), 5);
    }

    /**
     * @test
     */
    public function updateUpdatesSyncStatusOnFailure(): void
    {
        $url = sprintf('/checklist/%s', self::$checklist->getId());
        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => self::$deputySecret,
            'data' => ['syncStatus' => Checklist::SYNC_STATUS_PERMANENT_ERROR, 'syncError' => 'Permanent error occurred'],
        ]);

        self::assertEquals(self::$checklist->getId(), $response['data']['id']);
        self::assertEquals(Checklist::SYNC_STATUS_PERMANENT_ERROR, $response['data']['synchronisation_status']);
        self::assertEquals('Permanent error occurred', $response['data']['synchronisation_error']);
    }

    /**
     * @test
     */
    public function updateUpdatesUuidWhenGivenInRequest(): void
    {
        $url = sprintf('/checklist/%s', self::$checklist->getId());
        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => self::$deputySecret,
            'data' => ['uuid' => '1234-456789-0123'],
        ]);

        self::assertEquals(self::$checklist->getId(), $response['data']['id']);
        self::assertEquals('1234-456789-0123', $response['data']['uuid']);
    }
}
