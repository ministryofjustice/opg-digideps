<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\v2\Registration\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;
use Tests\OPG\Digideps\Backend\Integration\TestHelpers\OrgDeputyshipDTOTestHelper;
use Symfony\Component\HttpFoundation\Response;

class OrgDeputyshipControllerTest extends AbstractTestController
{
    private static $tokenAdmin;
    private $headers;

    public function setUp(): void
    {
        parent::setUp();

        if (self::$tokenAdmin === null) {
            self::$tokenAdmin = $this->loginAsAdmin();
        }

        $this->headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenAdmin];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    #[Test]
    public function create()
    {
        $orgDeputyshipJson = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(2, 0);
        self::$frameworkBundleClient->request('POST', '/v2/org-deputyships', [], [], $this->headers, $orgDeputyshipJson);

        /** @var Response $response */
        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $this->assertResponseHasArrayKeys($response);
    }

    private function assertResponseHasArrayKeys(Response $response): void
    {
        /** @var array<string, mixed> $decodedResponseContent */
        $decodedResponseContent = json_decode($response->getContent(), true)['data'];

        $this->assertArrayHasKey('errors', $decodedResponseContent);
        $this->assertArrayHasKey('added', $decodedResponseContent);

        self::assertIsArray($decodedResponseContent['added']);
        $this->assertArrayHasKey('clients', $decodedResponseContent['added']);
        $this->assertArrayHasKey('deputies', $decodedResponseContent['added']);
        $this->assertArrayHasKey('organisations', $decodedResponseContent['added']);
        $this->assertArrayHasKey('reports', $decodedResponseContent['added']);
    }


    #[Test]
    #[DataProvider('uploadProvider')]
    public function uploadProvidesFeedbackOnEntitiesProcessed(
        string $deputyshipsJson,
        int $expectedClients,
        int $expectedDeputies,
        int $expectedReports,
        int $expectedOrganisations,
        int $expectedErrors,
    ) {
        self::$frameworkBundleClient->request('POST', '/v2/org-deputyships', [], [], $this->headers, $deputyshipsJson);

        /** @var Response $response */
        $response = self::$frameworkBundleClient->getResponse();
        $actualUploadResults = json_decode($response->getContent(), true)['data'];

        self::assertCount($expectedClients, $actualUploadResults['added']['clients'], 'clients count was unexpected');
        self::assertCount($expectedDeputies, $actualUploadResults['added']['deputies'], 'deputies count was unexpected');
        self::assertCount($expectedReports, $actualUploadResults['added']['reports'], 'reports count was unexpected');
        self::assertCount($expectedOrganisations, $actualUploadResults['added']['organisations'], 'organisations count was unexpected');
        self::assertCount($expectedErrors, $actualUploadResults['errors']['messages'], 'errors count was unexpected');
    }

    public static function uploadProvider(): array
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

    #[Test]
    #[DataProvider('invalidPayloadProvider')]
    public function createExceedingBatchSizeReturns413(string $dtoJson)
    {
        self::$frameworkBundleClient->request('POST', '/v2/org-deputyships', [], [], $this->headers, $dtoJson);

        /** @var Response $response */
        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public static function invalidPayloadProvider(): array
    {
        return [
            'Too many records' => [OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(10001, 0)],
            'No records' => [OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(0, 0)],
        ];
    }
}
