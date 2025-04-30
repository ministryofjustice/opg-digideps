<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;

class ToolsControllerTest extends AbstractTestController
{
    private static User $previousDeputy;
    private static Client $previousClient;
    private static Report $previousReport1;
    private static Report $previousReport2;

    private static User $newDeputy;
    private static Client $newClient;
    private static Report $newReport1;

    private static ?string $tokenAdmin = null;
    private static ?string $tokenSuperAdmin = null;
    private static ?string $tokenLayDeputy = null;

    private ?array $headersAdmin = [];
    private ?array $headersSuperAdmin = [];
    private ?array $headersDeputy = [];

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenSuperAdmin = $this->loginAsSuperAdmin();
            self::$tokenLayDeputy = $this->loginAsDeputy();
        }

        // deputy 1
        self::$previousDeputy = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$previousClient = self::fixtures()->createClient(self::$previousDeputy, ['setCaseNumber' => '12345678']);
        self::$previousReport1 = self::fixtures()->createReport(self::$previousClient);
        self::$previousReport2 = self::fixtures()->createReport(self::$previousClient);

        // deputy 2
        self::$newDeputy = self::fixtures()->createUser();
        self::$newClient = self::fixtures()->createClient(self::$newDeputy, ['setCaseNumber' => '12345678']);
        self::$newReport1 = self::fixtures()->createReport(self::$newClient);

        self::$em = self::fixtures()->getEntityManager();
        self::fixtures()->flush()->clear();

        $this->headersAdmin = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenAdmin];
        $this->headersSuperAdmin = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenSuperAdmin];
        $this->headersDeputy = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenLayDeputy];
    }

    /**
     * @test
     */
    public function onlyAuthorisedUsersCanAccessToolsEndpoint()
    {
        self::$frameworkBundleClient->request(
            'POST',
            '/v2/tools/reassign-reports',
            [],
            [],
            $this->headersAdmin,
            '{"firstClientId": "1", "secondClientId": "2"}'
        );

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        self::$frameworkBundleClient->request(
            'POST',
            '/v2/tools/reassign-reports',
            [],
            [],
            $this->headersSuperAdmin,
            '{"firstClientId": "1", "secondClientId": "2"}'
        );

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function validPostRequest()
    {
        $previousClient = self::$em
            ->getRepository(Client::class)
            ->findOneBy(['id' => self::$previousClient->getId()]);

        $previousReports = $previousClient->getReports();

        $this->assertEquals(2, sizeof($previousReports));
        $reportIds = array_map(function ($r) { return $r->getId(); }, $previousReports->toArray());
        $this->assertContains(self::$previousReport1->getId(), $reportIds);
        $this->assertContains(self::$previousReport2->getId(), $reportIds);

        $newClient = self::$em
            ->getRepository(Client::class)
            ->findOneBy(['id' => self::$newClient->getId()]);

        $newReports = $newClient->getReports();

        $this->assertEquals(1, sizeof($newReports));
        $this->assertEquals(self::$newReport1->getId(), $newReports[0]->getId());

        $content = sprintf('{"firstClientId": "%d", "secondClientId": "%d"}', self::$previousClient->getId(), self::$newClient->getId());
        self::$frameworkBundleClient->request(
            'POST',
            '/v2/tools/reassign-reports',
            [],
            [],
            $this->headersSuperAdmin,
            $content
        );

        self::fixtures()->flush()->clear();

        $previousClient = self::$em
            ->getRepository(Client::class)
            ->findOneBy(['id' => self::$previousClient->getId()]);

        $reassignedReports = $previousClient->getReports();

        $this->assertEquals(1, sizeof($reassignedReports));
        $this->assertEquals(self::$newReport1->getId(), $reassignedReports[0]->getId());

        $newClient = self::$em
            ->getRepository(Client::class)
            ->findOneBy(['id' => self::$newClient->getId()]);

        $reassignedReports = $newClient->getReports();

        $this->assertEquals(2, sizeof($reassignedReports));
        $reportIds = array_map(function ($r) { return $r->getId(); }, $reassignedReports->toArray());
        $this->assertContains(self::$previousReport1->getId(), $reportIds);
        $this->assertContains(self::$previousReport2->getId(), $reportIds);
    }

    /**
     * @test
     *
     * @dataProvider invalidPostDataProvider
     */
    public function invalidClientIdsPostRequest(string $content, int $expectedStatusCode, string $expectedResponse)
    {
        self::$frameworkBundleClient->request(
            'POST',
            '/v2/tools/reassign-reports',
            [],
            [],
            $this->headersSuperAdmin,
            $content
        );

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    /**
     * Provides the content of the POST request, the expected status code and the expected response message.
     *
     * @return array
     */
    public function invalidPostDataProvider()
    {
        return [
            [
                '{"firstClientId": "", "secondClientId": ""}',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                '{"success":false,"message":"The client ids provided are not valid numbers!"}',
            ],
            [
                '{"firstClientId": "1", "secondClientId": "A"}',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                '{"success":false,"message":"The client ids provided are not valid numbers!"}',
            ],
            [
                '{"firstClientId": "1", "secondClientId": "1"}',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                '{"success":false,"message":"The client ids provided are the same!"}',
            ],
            [
                '{"firstClientId": "999999", "secondClientId": "1"}',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                '{"success":false,"message":"First Client with id 999999 not found"}',
            ],
            [
                '{"firstClientId": "1", "secondClientId": "101010"}',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                '{"success":false,"message":"Second Client with id 101010 not found"}',
            ],
        ];
    }

    /**
     * @test
     */
    public function clientsWithDifferentCaseNumbers()
    {
        $newClient = self::$em
            ->getRepository(Client::class)
            ->findOneBy(['id' => self::$newClient->getId()]);

        $newClient->setCaseNumber('12121212');

        self::fixtures()->flush()->clear();

        $content = sprintf('{"firstClientId": "%d", "secondClientId": "%d"}', self::$previousClient->getId(), self::$newClient->getId());
        self::$frameworkBundleClient->request(
            'POST',
            '/v2/tools/reassign-reports',
            [],
            [],
            $this->headersSuperAdmin,
            $content
        );

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('{"success":false,"message":"The clients have two different case numbers!"}', $response->getContent());
    }
}
