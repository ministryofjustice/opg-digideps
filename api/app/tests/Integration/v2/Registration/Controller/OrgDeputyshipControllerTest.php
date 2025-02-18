<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\Controller;

use App\Tests\Unit\Controller\AbstractTestController;
use App\Tests\Unit\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class OrgDeputyshipControllerTest extends AbstractTestController
{
    private static $tokenAdmin;
    private $headers;

    /** @var Client */
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
        }

        $this->headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenAdmin];
    }

    /** @test */
    public function create()
    {
        $orgDeputyshipJson = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(2, 0);
        $this->client->request('POST', '/v2/org-deputyships', [], [], $this->headers, $orgDeputyshipJson);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $this->assertResponseHasArrayKeys($this->client->getResponse());
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
        $this->client->request('POST', '/v2/org-deputyships', [], [], $this->headers, $deputyshipsJson);

        $actualUploadResults = json_decode($this->client->getResponse()->getContent(), true)['data'];

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
        $this->client->request('POST', '/v2/org-deputyships', [], [], $this->headers, $dtoJson);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client->getResponse()->getStatusCode());
    }

    public function invalidPayloadProvider()
    {
        return [
            'Too many records' => [OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(10001, 0)],
            'No records' => [OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipCompressedJson(0, 0)],
        ];
    }
}
