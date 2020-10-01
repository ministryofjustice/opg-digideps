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

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
        }

        $this->headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenAdmin];
    }

    /**
     * @test
     */
    public function create()
    {
        $client = static::createClient(['environment' => 'test', 'debug' => false]);

        $orgDeputyshipJson = OrgDeputyshipDTOTestHelper::generateCasRecOrgDeputyshipJson(2, 0);
        $client->request('POST', '/v2/org-deputyships', [], [], $this->headers, $orgDeputyshipJson);

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());

        $decodedResponseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $decodedResponseContent);
        $this->assertArrayHasKey('added', $decodedResponseContent);
        $this->assertArrayHasKey('clients', $decodedResponseContent['added']);
        $this->assertArrayHasKey('discharged_clients', $decodedResponseContent['added']);
        $this->assertArrayHasKey('named_deputies', $decodedResponseContent['added']);
        $this->assertArrayHasKey('reports', $decodedResponseContent['added']);
    }
}
