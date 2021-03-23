<?php declare(strict_types=1);

namespace Tests\App\Controller;

use App\Entity\Report\Checklist;
use DateTime;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class ChecklistControllerTest extends AbstractTestController
{
    private static $deputy;
    private static $client;
    private static $report;
    private static $checklist;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$deputy = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client = self::fixtures()->createClient(self::$deputy, ['setFirstname' => 'CL']);
        self::$report = self::fixtures()->createReport(self::$client);
        self::$checklist = self::fixtures()->createChecklist(self::$report);
        self::fixtures()->flush();
    }

    /**
     * @test
     */
    public function update_usesSecretBasedAuth(): void
    {
        $return = $this->assertJsonRequest('PUT', '/checklist/32', [
            'mustFail' => true,
            'ClientSecret' => 'WRONG CLIENT SECRET',
            'assertCode' => 403,
            'assertResponseCode' => 403,
            'data' => []
        ]);

        $this->assertStringContainsString('client secret not accepted', $return['message']);
    }

    /**
     * @test
     */
    public function update_updatesSyncStatusOnSuccess(): void
    {
        $url = sprintf('/checklist/%s', self::$checklist->getId());
        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['syncStatus' => Checklist::SYNC_STATUS_SUCCESS]
        ]);

        self::assertEquals(self::$checklist->getId(), $response['data']['id']);
        self::assertEquals(Checklist::SYNC_STATUS_SUCCESS, $response['data']['synchronisation_status']);
        self::assertEqualsWithDelta((new \DateTime())->getTimestamp(), (new \Datetime($response['data']['synchronisation_time']))->getTimestamp(), 5);
    }

    /**
     * @test
     */
    public function update_updatesSyncStatusOnFailure(): void
    {
        $url = sprintf('/checklist/%s', self::$checklist->getId());
        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['syncStatus' => Checklist::SYNC_STATUS_PERMANENT_ERROR, 'syncError' => 'Permanent error occurred']
        ]);

        self::assertEquals(self::$checklist->getId(), $response['data']['id']);
        self::assertEquals(Checklist::SYNC_STATUS_PERMANENT_ERROR, $response['data']['synchronisation_status']);
        self::assertEquals('Permanent error occurred', $response['data']['synchronisation_error']);
    }

    /**
     * @test
     */
    public function update_updatesUuidWhenGivenInRequest(): void
    {
        $url = sprintf('/checklist/%s', self::$checklist->getId());
        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['uuid' => '1234-456789-0123']
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
