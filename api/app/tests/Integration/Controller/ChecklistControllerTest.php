<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Entity\Report\Checklist;
use app\tests\Integration\Controller\AbstractTestController;

class ChecklistControllerTest extends AbstractTestController
{
    private static $deputy;
    private static $client;
    private static $report;
    private static $checklist;

    public function setUp(): void
    {
        parent::setUp();

        self::$deputy = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client = self::fixtures()->createClient(self::$deputy, ['setFirstname' => 'CL']);
        self::$report = self::fixtures()->createReport(self::$client);
        self::$checklist = self::fixtures()->createChecklist(self::$report);
        self::fixtures()->flush();
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
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['syncStatus' => Checklist::SYNC_STATUS_SUCCESS],
        ]);

        self::assertEquals(self::$checklist->getId(), $response['data']['id']);
        self::assertEquals(Checklist::SYNC_STATUS_SUCCESS, $response['data']['synchronisation_status']);
        self::assertEqualsWithDelta((new \DateTime())->getTimestamp(), (new \DateTime($response['data']['synchronisation_time']))->getTimestamp(), 5);
    }

    /**
     * @test
     */
    public function updateUpdatesSyncStatusOnFailure(): void
    {
        $url = sprintf('/checklist/%s', self::$checklist->getId());
        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
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
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['uuid' => '1234-456789-0123'],
        ]);

        self::assertEquals(self::$checklist->getId(), $response['data']['id']);
        self::assertEquals('1234-456789-0123', $response['data']['uuid']);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }
}
