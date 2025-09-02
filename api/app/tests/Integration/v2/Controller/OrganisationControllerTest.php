<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Controller;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Entity\Organisation;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\Controller\AbstractTestController;
use Symfony\Component\HttpFoundation\Response;

final class OrganisationControllerTest extends AbstractTestController
{
    private array $headers = [];
    private array $headersSuperAdmin = [];
    private array $headersDeputy = [];

    /** @var []Organisation */
    private static array $orgs;

    private static User $profUser;
    private static ?string $tokenAdmin;
    private static ?string $tokenSuperAdmin;
    private static ?string $tokenDeputyInOrg;
    private static bool $isSetup = false;

    public static function setUpBeforeClass(): void
    {
        // This is here to prevent the default setup until tests that fail with it are altered
    }

    public function setUp(): void
    {
        parent::setUp();

        self::setupFixtures();

        self::$fixtures::deleteReportsData(['organisation']);

        self::$orgs = self::fixtures()->createOrganisations(4);

        self::fixtures()->flush()->clear();

        self::$profUser = self::fixtures()->getRepo('User')->findOneByEmail('prof@example.org');
        self::fixtures()->addUserToOrganisation(self::$profUser->getId(), end(self::$orgs)->getId());
        self::fixtures()->flush()->clear();

        self::$em = self::fixtures()->getEntityManager();

        if(!self::$isSetup) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenSuperAdmin = $this->loginAsSuperAdmin();
            self::$tokenDeputyInOrg = $this->loginAsProf();
            self::$isSetup = true;
        }

        $this->headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenAdmin];
        $this->headersSuperAdmin = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenSuperAdmin];
        $this->headersDeputy = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenDeputyInOrg];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    #[Test]
    public function getAllActionReturnsAllOrganisations(): void
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/list', [], [], $this->headers);

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertTrue($responseContent['success']);
        $this->assertCount(4, $responseContent['data']);
    }

    #[Test]
    public function getByIdActionReturnsOrganisationsIfFound(): void
    {
        $org = self::$orgs[0];
        self::$frameworkBundleClient->request('GET', '/v2/organisation/'.$org->getId(), [], [], $this->headers);

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertTrue($responseContent['success']);
        $this->assertEquals($org->getId(), $responseContent['data']['id']);
        $this->assertEquals($org->getName(), $responseContent['data']['name']);
        $this->assertEquals($org->getEmailIdentifier(), $responseContent['data']['email_identifier']);
        $this->assertTrue($responseContent['data']['is_activated']);
    }

    #[Test]
    public function getByIdActionReturns404IfNotFound(): void
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/99999', [], [], $this->headers);

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertFalse($responseContent['success']);
    }

    #[Test]
    public function getByIdActionReturnsForbiddenForDeputiesNotInOrganisation(): void
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/'.self::$orgs[0]->getId(), [], [], $this->headersDeputy);

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    public function getByIdActionAllowsDeputiesFetchTheirOwnOrganisation(): void
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/'.end(self::$orgs)->getId(), [], [], $this->headersDeputy);

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    public function createActionCreatesAnOrganisation(): void
    {
        self::$frameworkBundleClient->request(
            'POST',
            '/v2/organisation',
            [],
            [],
            $this->headers,
            '{"name": "Org Name", "email_identifier": "email_id", "is_activated": true}'
        );

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertTrue($responseContent['success']);

        $organisation = self::$em
            ->getRepository(Organisation::class)
            ->findOneBy(['emailIdentifier' => 'email_id']);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Org Name', $organisation->getName());
        $this->assertEquals('email_id', $organisation->getEmailIdentifier());
        $this->assertTrue($organisation->IsActivated());
    }


    #[DataProvider('getBadRequestData')]
    #[Test]
    public function createActionReturnsBadRequestIfGivenBadData(string $data): void
    {
        self::$frameworkBundleClient->request(
            'POST',
            '/v2/organisation',
            [],
            [],
            $this->headers,
            $data
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    public function createActionReturnsBadRequestIfGivenExistingEmailIdentifier(): void
    {
        self::$frameworkBundleClient->request(
            'POST',
            '/v2/organisation',
            [],
            [],
            $this->headers,
            '{"name": "Org Name", "email_identifier": "email_id", "is_activated": true}'
        );

        self::$frameworkBundleClient->request(
            'POST',
            '/v2/organisation',
            [],
            [],
            $this->headers,
            '{"name": "Org Name", "email_identifier": "email_id", "is_activated": true}'
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    public function updateActionUpdatesAnOrganisation(): void
    {
        $orgId = self::$orgs[0]->getId();
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/'.$orgId,
            [],
            [],
            $this->headers,
            '{"name": "Org Name Updated", "email_identifier": "email_id_updated", "is_activated": false}'
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $organisation = self::$em
            ->getRepository(Organisation::class)
            ->findOneBy(['id' => $orgId]);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Org Name Updated', $organisation->getName());
        $this->assertEquals('email_id_updated', $organisation->getEmailIdentifier());
        $this->assertFalse($organisation->IsActivated());
    }


    #[DataProvider('getBadRequestData')]
    #[Test]
    public function updateActionReturnsBadRequestIfGivenBadData(string $data): void
    {
        $orgId = self::$orgs[1]->getId();
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/'.$orgId,
            [],
            [],
            $this->headers,
            $data
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public static function getBadRequestData(): array
    {
        return [
            ['data' => '{"name": "Org Name", "email_identifier": "unique_id"}'],
            ['data' => '{"name": "Org Name", "is_activated": true}'],
            ['data' => '{"name": "Org Name", "email_identifier": "", "is_activated": true}'],
            ['data' => '{"name": "Org Name", "email_identifier": null, "is_activated": true}'],
        ];
    }

    #[Test]
    public function updateActionReturnsBadRequestIfGivenExistingEmailIdentifier(): void
    {
        self::$frameworkBundleClient->request(
            'POST',
            '/v2/organisation',
            [],
            [],
            $this->headers,
            '{"name": "Org Name", "email_identifier": "org_email_3", "is_activated": true}'
        );

        $orgId = self::$orgs[1]->getId();

        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/'.$orgId,
            [],
            [],
            $this->headers,
            '{"name": "Org 2", "email_identifier": "org_email_3", "is_activated": true}'
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    public function deleteActionDeletesOrganisation(): void
    {
        $orgId = self::$orgs[2]->getId();
        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/'.$orgId,
            [],
            [],
            $this->headersSuperAdmin
        );

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertTrue($responseContent['success']);
        $this->assertEquals('Organisation deleted', $responseContent['message']);

        $organisation = self::$em
            ->getRepository(Organisation::class)
            ->find($orgId);

        $this->assertNotNull($organisation);
        $this->assertNotNull($organisation->getDeletedAt());
        $this->assertTrue($organisation->isDeleted());
    }

    #[Test]
    public function adminsCannotDeleteOrganisation(): void
    {
        $orgId = self::$orgs[0]->getId();
        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/'.$orgId,
            [],
            [],
            $this->headers
        );

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    public function addUserActionAddsUserToOrganisation(): void
    {
        $orgId = self::$orgs[0]->getId();
        $newUser = self::fixtures()->getRepo('User')->findOneBy([], ['id' => 'ASC']);

        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/'.$orgId.'/user/'.$newUser->getId(),
            [],
            [],
            $this->headers
        );

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());

        $organisation = self::$em
            ->getRepository(Organisation::class)
            ->findOneBy(['id' => $orgId]);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals(1, count($organisation->getUsers()));
        $this->assertContains($newUser, $organisation->getUsers());
    }

    #[Test]
    public function addUserActionReturnsNotFoundOnInvalidOrganisationId(): void
    {
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/9004/user/3',
            [],
            [],
            $this->headers
        );

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertFalse($responseContent['success']);
    }

    #[Test]
    public function addUserActionReturnsBadRequestOnInvalidUserId(): void
    {
        $orgId = self::$orgs[0]->getId();
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/'.$orgId.'/user/9003',
            [],
            [],
            $this->headers
        );

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertFalse($responseContent['success']);
        $this->assertEquals('Invalid user id', $responseContent['message']);
    }

    #[Test]
    public function addUserActionReturnsForbiddenForUsersNotInOrganisation(): void
    {
        $orgId = self::$orgs[0]->getId();
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/'.$orgId.'/user/'.self::$profUser->getId(),
            [],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    public function addUserActionAllowsUsersToAddToTheirOrganisation(): void
    {
        $orgId = end(self::$orgs)->getId();

        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/'.$orgId.'/user/'.self::$profUser->getId(),
            [],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    public function removeUserActionRemovesUserFromOrganisation(): void
    {
        $orgId = self::$orgs[0]->getId();
        $newUser = self::fixtures()->getRepo('User')->findOneBy([], ['id' => 'DESC']);

        self::fixtures()->addUserToOrganisation($newUser->getId(), $orgId);
        self::fixtures()->flush()->clear();

        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/'.$orgId.'/user/'.$newUser->getId(),
            [],
            [],
            $this->headers
        );

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertTrue($responseContent['success']);
        $this->assertEquals('User removed', $responseContent['message']);

        $organisation = self::$em
            ->getRepository(Organisation::class)
            ->findOneBy(['id' => $orgId]);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertTrue(!in_array($newUser, $organisation->getUsers()->toArray()));
    }

    #[Test]
    public function removeUserActionReturnsNotFoundOnInvalidOrganisationId(): void
    {
        $user = self::fixtures()->getRepo('User')->findOneBy([], ['id' => 'DESC']);
        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/9001/user/'.$user->getId(),
            [],
            [],
            $this->headers
        );

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertFalse($responseContent['success']);
    }

    #[Test]
    public function removeUserActionReturnsBadRequestOnInvalidUserId(): void
    {
        $orgId = self::$orgs[0]->getId();

        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/'.$orgId.'/user/9003',
            [],
            [],
            $this->headers
        );

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertFalse($responseContent['success']);
        $this->assertEquals('Invalid user id', $responseContent['message']);
    }

    #[Test]
    public function removeUserActionReturnsForbiddenForUsersNotInOrganisation(): void
    {
        $orgId = self::$orgs[0]->getId();
        $user = self::fixtures()->getRepo('User')->findOneBy([], ['id' => 'DESC']);

        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/'.$orgId.'/user/'.$user->getId(),
            [],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    public function removeUserActionAllowsUserRemoveFromTheirOrganisation(): void
    {
        $orgId = self::$orgs[0]->getId();

        /** @var UserRepository $repo */
        $repo = self::fixtures()->getRepo(User::class);

        $newUser = $repo->findOneByEmail('prof@example.org');

        self::fixtures()->addUserToOrganisation($newUser->getId(), $orgId);
        self::fixtures()->flush()->clear();

        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/'.$orgId.'/user/'.$newUser->getId(),
            [],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    public function getUsersReturnsTheCorrectAmountOfUsers(): void
    {
        $orgId = end(self::$orgs)->getId();

        for ($x = 0; $x < 10; ++$x) {
            $newUser = self::fixtures()->createUser(roleName:  User::ROLE_PROF);

            self::fixtures()->flush()->clear();

            self::fixtures()->addUserToOrganisation($newUser->getId(), $orgId);
            self::fixtures()->flush()->clear();
        }

        self::$frameworkBundleClient->request(
            'GET',
            '/v2/organisation/'.$orgId.'/users',
            [],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseContent = json_decode($response->getContent(), true);
        $this->assertCount(11, $responseContent['data']['records']);

        self::$frameworkBundleClient->request(
            'GET',
            '/v2/organisation/'.$orgId.'/users',
            ['offset' => 0, 'limit' => 7],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseContent = json_decode($response->getContent(), true);
        $this->assertCount(7, $responseContent['data']['records']);

        self::$frameworkBundleClient->request(
            'GET',
            '/v2/organisation/'.$orgId.'/users',
            ['offset' => 7, 'limit' => 7],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseContent = json_decode($response->getContent(), true);
        $this->assertCount(4, $responseContent['data']['records']);
    }

    #[Test]
    public function getClientsReturnsTheCorrectAmountOfClients(): void
    {
        $orgId = end(self::$orgs)->getId();

        for ($x = 0; $x < 10; ++$x) {
            $newClient = self::fixtures()->createClient();
            self::fixtures()->flush()->clear();

            self::fixtures()->addClientToOrganisation($newClient->getId(), $orgId);
            self::fixtures()->flush()->clear();
        }

        self::$frameworkBundleClient->request(
            'GET',
            '/v2/organisation/'.$orgId.'/clients',
            [],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseContent = json_decode($response->getContent(), true);
        $this->assertCount(10, $responseContent['data']['records']);

        self::$frameworkBundleClient->request(
            'GET',
            '/v2/organisation/'.$orgId.'/clients',
            ['offset' => 0, 'limit' => 7],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseContent = json_decode($response->getContent(), true);
        $this->assertCount(7, $responseContent['data']['records']);

        self::$frameworkBundleClient->request(
            'GET',
            '/v2/organisation/'.$orgId.'/clients',
            ['offset' => 7, 'limit' => 7],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseContent = json_decode($response->getContent(), true);
        $this->assertCount(3, $responseContent['data']['records']);
    }
}
