<?php

namespace App\Tests\Integration\v2\Controller;

use App\Entity\Organisation;
use App\Entity\User;
use App\Tests\Integration\Controller\AbstractTestController;
use Symfony\Component\HttpFoundation\Response;

class OrganisationControllerTest extends AbstractTestController
{
    /** @var array */
    private $headers = [];

    /** @var array */
    private $headersSuperAdmin = [];

    /** @var array */
    private $headersDeputy = [];

    /** @var []Organisation */
    private static $orgs;

    /** @var User */
    private static $profUser;

    /** @var string|null */
    private static $tokenAdmin;

    /** @var string|null */
    private static $tokenSuperAdmin;

    /** @var string|null */
    private static $tokenDeputyInOrg;

    public function setUp(): void
    {
        parent::setUp();
        self::$fixtures::deleteReportsData(['organisation']);

        self::$orgs = self::fixtures()->createOrganisations(4);

        self::fixtures()->flush()->clear();

        self::$profUser = self::fixtures()->getRepo('User')->findOneByEmail('prof@example.org');
        self::fixtures()->addUserToOrganisation(self::$profUser->getId(), end(self::$orgs)->getId());
        self::fixtures()->flush()->clear();

        self::$em = self::fixtures()->getEntityManager();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
        }

        if (null === self::$tokenSuperAdmin) {
            self::$tokenSuperAdmin = $this->loginAsSuperAdmin();
        }

        if (null === self::$tokenDeputyInOrg) {
            self::$tokenDeputyInOrg = $this->loginAsProf();
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

    /**
     * @test
     */
    public function getAllActionReturnsAllOrganisations()
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/list', [], [], $this->headers);

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertTrue($responseContent['success']);
        $this->assertCount(4, $responseContent['data']);
    }

    /**
     * @test
     */
    public function getByIdActionReturnsOrganisationsIfFound()
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

    /**
     * @test
     */
    public function getByIdActionReturns404IfNotFound()
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/99999', [], [], $this->headers);

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertFalse($responseContent['success']);
    }

    /**
     * @test
     */
    public function getByIdActionReturnsForbiddenForDeputiesNotInOrganisation()
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/'.self::$orgs[0]->getId(), [], [], $this->headersDeputy);

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function getByIdActionAllowsDeputiesFetchTheirOwnOrganisation()
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/'.end(self::$orgs)->getId(), [], [], $this->headersDeputy);

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function createActionCreatesAnOrganisation()
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

    /**
     * @test
     *
     * @dataProvider getBadRequestData
     */
    public function createActionReturnsBadRequestIfGivenBadData($data)
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

    /**
     * @test
     */
    public function createActionReturnsBadRequestIfGivenExistingEmailIdentifier()
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

    /**
     * @test
     */
    public function updateActionUpdatesAnOrganisation()
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

    /**
     * @test
     *
     * @dataProvider getBadRequestData
     */
    public function updateActionReturnsBadRequestIfGivenBadData($data)
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

    /**
     * @return array
     */
    public function getBadRequestData()
    {
        return [
            ['data' => '{"name": "Org Name", "email_identifier": "unique_id"}'],
            ['data' => '{"name": "Org Name", "is_activated": true}'],
            ['data' => '{"name": "Org Name", "email_identifier": "", "is_activated": true}'],
            ['data' => '{"name": "Org Name", "email_identifier": null, "is_activated": true}'],
        ];
    }

    /**
     * @test
     */
    public function updateActionReturnsBadRequestIfGivenExistingEmailIdentifier()
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

    /**
     * @test
     */
    public function deleteActionDeletesOrganisation()
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

    /**
     * @test
     */
    public function adminsCannotDeleteOrganisation()
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

    /**
     * @test
     */
    public function addUserActionAddsUserToOrganisation()
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

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $organisation = self::$em
            ->getRepository(Organisation::class)
            ->findOneBy(['id' => $orgId]);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals(1, count($organisation->getUsers()));
        $this->assertContains($newUser, $organisation->getUsers());
    }

    /**
     * @test
     */
    public function addUserActionReturnsNotFoundOnInvalidOrganisationId()
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

    /**
     * @test
     */
    public function addUserActionReturnsBadRequestOnInvalidUserId()
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

    /**
     * @test
     */
    public function addUserActionReturnsForbiddenForUsersNotInOrganisation()
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

    /**
     * @test
     */
    public function addUserActionAllowsUsersToAddToTheirOrganisation()
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

    /**
     * @test
     */
    public function removeUserActionRemovesUserFromOrganisation()
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

    /**
     * @test
     */
    public function removeUserActionReturnsNotFoundOnInvalidOrganisationId()
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

    /**
     * @test
     */
    public function removeUserActionReturnsBadRequestOnInvalidUserId()
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

    /**
     * @test
     */
    public function removeUserActionReturnsForbiddenForUsersNotInOrganisation()
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

    /**
     * @test
     */
    public function removeUserActionAllowsUserRemoveFromTheirOrganisation()
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
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function getUsersReturnsTheCorrectAmountOfUsers()
    {
        $orgId = end(self::$orgs)->getId();

        for ($x = 0; $x < 10; ++$x) {
            $newUser = self::fixtures()->createUser(['setRoleName' => User::ROLE_PROF]);

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

    /**
     * @test
     */
    public function getClientsReturnsTheCorrectAmountOfClients()
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
