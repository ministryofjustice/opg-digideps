<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\Controller;

use App\Tests\Integration\Controller\AbstractTestController;
use App\Tests\Integration\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;
use Symfony\Component\HttpFoundation\Response;

class OrgDeputyshipControllerTest extends AbstractTestController
{
    private static $tokenAdmin;
    private $headers;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
        }

        $this->headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenAdmin];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    /** @test */
    public function create()
    {
        $orgDeputyshipJson = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(2, 0);
        self::$frameworkBundleClient->request('POST', '/v2/org-deputyships', [], [], $this->headers, $orgDeputyshipJson);

        $this->assertEquals(Response::HTTP_OK, self::$frameworkBundleClient->getResponse()->getStatusCode());
        $this->assertJson(self::$frameworkBundleClient->getResponse()->getContent());

        $this->assertResponseHasArrayKeys(self::$frameworkBundleClient->getResponse());
    }

    private function assertResponseHasArrayKeys(Response $response)
    {
        $decodedResponseContent = json_decode($response->getContent(), true)['data'];

        $this->assertArrayHasKey('errors', $decodedResponseContent);
        $this->assertArrayHasKey('added', $decodedResponseContent);
        $this->assertArrayHasKey('clients', $decodedResponseContent['added']);
        $this->assertArrayHasKey('deputies', $decodedResponseContent['added']);
        $this->assertArrayHasKey('organisations', $decodedResponseContent['added']);
        $this->assertArrayHasKey('reports', $decodedResponseContent['added']);
    }

    /**
     * @test
     *
     * @dataProvider uploadProvider
     */
    public function uploadProvidesFeedbackOnEntitiesProcessed(
        string $deputyshipsJson,
        int $expectedClients,
        int $expectedDeputies,
        int $expectedReports,
        int $expectedOrganisations,
        int $expectedErrors,
    ) {
        self::$frameworkBundleClient->request('POST', '/v2/org-deputyships', [], [], $this->headers, $deputyshipsJson);

        $actualUploadResults = json_decode(self::$frameworkBundleClient->getResponse()->getContent(), true)['data'];

        self::assertCount($expectedClients, $actualUploadResults['added']['clients'], 'clients count was unexpected');
        self::assertCount($expectedDeputies, $actualUploadResults['added']['deputies'], 'deputies count was unexpected');
        self::assertCount($expectedReports, $actualUploadResults['added']['reports'], 'reports count was unexpected');
        self::assertCount($expectedOrganisations, $actualUploadResults['added']['organisations'], 'organisations count was unexpected');
        self::assertCount($expectedErrors, $actualUploadResults['errors']['messages'], 'errors count was unexpected');
    }

    public function uploadProvider()
    {
        return [
            '3 valid Org Deputyships' => [
                OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(3, 0), 3, 3, 3, 3, 0,
            ],
            '2 valid, 1 invalid Org Deputyships' => [
                OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(2, 1), 2, 2, 2, 2, 1,
            ],
        ];
    }

    /**
     * @dataProvider invalidPayloadProvider
     *
     * @test
     */
    public function createExceedingBatchSizeReturns413(string $dtoJson)
    {
        self::$frameworkBundleClient->request('POST', '/v2/org-deputyships', [], [], $this->headers, $dtoJson);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, self::$frameworkBundleClient->getResponse()->getStatusCode());
    }

    public function invalidPayloadProvider()
    {
        return [
            'Too many records' => [OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(10001, 0)],
            'No records' => [OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(0, 0)],
        ];
    }
}
