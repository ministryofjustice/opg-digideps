<?php

namespace App\Tests\Unit\v2\Tools\Controller;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Tests\Unit\Controller\AbstractTestController;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;

class ToolsControllerTest extends AbstractTestController
{
    private static EntityManager $em;

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

    private array $headersAdmin = [];
    private array $headersSuperAdmin = [];
    private array $headersDeputy = [];

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();
        self::$fixtures::deleteReportsData(['client']);

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

    // Test that admins don't have access, and that super-admins do
    /**
     * @test
     */
    public function onlyAuthorisedUsersCanAccessToolsEndpoint()
    {
        var_dump('Pre');
        self::$frameworkBundleClient->request(
            'GET',
            '/v2/tools/reassign-reports',
            [],
            [],
            $this->headersAdmin,
            '{"firstClientId": "1", "secondClientId": "2"}'
        );

        var_dump('Post');
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

    // Assert reports change over
    /**
     * @test
     */
    public function updateActionUpdatesAnOrganisation()
    {
        $previousClient = self::$em
            ->getRepository(ClientRepository::class)
            ->findOneBy(['id' => self::$previousClient->getId()]);

        $previousReports = $previousClient->getReports();

        $this->assertEquals(2, sizeof($previousReports));
        $reportIds = array_map(function ($r) { return $r->getId(); }, $previousReports);
        $this->assertContains(self::$previousReport1->getId(), $reportIds);
        $this->assertContains(self::$previousReport2->getId(), $reportIds);

        $newClient = self::$em
            ->getRepository(ClientRepository::class)
            ->findOneBy(['id' => self::$newClient->getId()]);

        $newReports = $newClient->getReports();

        $this->assertEquals(1, sizeof($newReports));
        $this->assertContains(self::$newReport1->getId(), $newReports[0]->getId());

        $content = sprintf('{"firstClientId": "%d", "secondClientId": "%d"}', self::$previousClient->getId(), self::$newClient->getId());
        self::$frameworkBundleClient->request(
            'POST',
            '/v2/tools/reassign-reports',
            [],
            [],
            $this->headersSuperAdmin,
            $content
        );

        $previousClient = self::$em
            ->getRepository(ClientRepository::class)
            ->findOneBy(['id' => self::$previousClient->getId()]);

        $reassignedReports = $previousClient->getReports();

        $this->assertEquals(1, sizeof($reassignedReports));
        $this->assertContains(self::$newReport1->getId(), $reassignedReports[0]->getId());

        $newClient = self::$em
            ->getRepository(ClientRepository::class)
            ->findOneBy(['id' => self::$newClient->getId()]);

        $reassignedReports = $newClient->getReports();

        $this->assertEquals(2, sizeof($reassignedReports));
        $reportIds = array_map(function ($r) { return $r->getId(); }, $reassignedReports);
        $this->assertContains(self::$previousReport1->getId(), $reportIds);
        $this->assertContains(self::$previousReport2->getId(), $reportIds);
    }
}
