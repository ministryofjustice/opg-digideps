<?php declare(strict_types=1);


namespace Tests\AppBundle\v2\Registration\Controller;

use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\AbstractTestController;
use Tests\AppBundle\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;

class OrgDeputyshipControllerTest extends AbstractTestController
{
    private static $tokenAdmin = null;
    private $headers = null;

    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    private $client;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
        }

        $this->headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenAdmin];
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
    }

    /** @test */
    public function create()
    {
        $orgDeputyshipJson = OrgDeputyshipDTOTestHelper::generateCasRecOrgDeputyshipJson(2, 0);
        $this->client->request('POST', '/v2/org-deputyships', [], [], $this->headers, $orgDeputyshipJson);

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $this->assertResponseHasArrayKeys($this->client->getResponse());
    }

    private function assertResponseHasArrayKeys(Response $response)
    {
        $decodedResponseContent = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('errors', $decodedResponseContent);
        $this->assertArrayHasKey('added', $decodedResponseContent);
        $this->assertArrayHasKey('clients', $decodedResponseContent['added']);
        $this->assertArrayHasKey('discharged_clients', $decodedResponseContent['added']);
        $this->assertArrayHasKey('named_deputies', $decodedResponseContent['added']);
        $this->assertArrayHasKey('organisations', $decodedResponseContent['added']);
        $this->assertArrayHasKey('reports', $decodedResponseContent['added']);
    }

    /**
     * @test
     * @dataProvider uploadProvider
     */
    public function upload_provides_feedback_on_entities_processed(
        string $deputyshipsJson,
        int $expectedClients,
        int $expectedDischargedClients,
        int $expectedNamedDeputies,
        int $expectedReports,
        int $expectedOrganisations,
        int $expectedErrors
    ) {
        $this->client->request('POST', '/v2/org-deputyships', [], [], $this->headers, $deputyshipsJson);

        $actualUploadResults = json_decode($this->client->getResponse()->getContent(), true);

        self::assertCount($expectedClients, $actualUploadResults['added']['clients'], 'clients count was unexpected');
        self::assertCount($expectedDischargedClients, $actualUploadResults['added']['discharged_clients'], 'discharged_clients count was unexpected');
        self::assertCount($expectedNamedDeputies, $actualUploadResults['added']['named_deputies'], 'named_deputies count was unexpected');
        self::assertCount($expectedReports, $actualUploadResults['added']['reports'], 'reports count was unexpected');
        self::assertCount($expectedOrganisations, $actualUploadResults['added']['organisations'], 'organisations count was unexpected');
        self::assertEquals($expectedErrors, $actualUploadResults['errors'], 'errors count was unexpected');
    }

    // add extra field in array for orgs created
    public function uploadProvider()
    {
        return [
            '3 valid Org Deputyships' =>
                [
                    OrgDeputyshipDTOTestHelper::generateCasRecOrgDeputyshipJson(3, 0), 3, 0, 3, 3, 3, 0
                ],
            '2 valid, 1 invalid Org Deputyships' =>
                [
                    OrgDeputyshipDTOTestHelper::generateCasRecOrgDeputyshipJson(2, 1), 2, 0, 2, 2, 2, 1
                ]
        ];
    }
}
