<?php

namespace Tests\App\v2\Controller;

use App\Entity\Organisation;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\AbstractTestController;

class OrganisationControllerTest extends AbstractTestController
{
    /** @var array */
    private $headers = [];

    /** @var array */
    private $headersSuperAdmin = [];

    /** @var array */
    private $headersDeputy = [];

    /** @var EntityManager */
    private static $em;

    /** @var null|string */
    private static $tokenAdmin = null;

    /** @var null|string */
    private static $tokenSuperAdmin = null;

    /** @var null|string */
    private static $tokenDeputyInOrg = null;

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::fixtures()->createOrganisations(4);
        self::fixtures()->flush()->clear();

        $profUser = self::fixtures()->getRepo('User')->findOneByEmail('prof@example.org');
        self::fixtures()->addUserToOrganisation($profUser->getId(), 4);
        self::fixtures()->flush()->clear();

        self::$em = self::$frameworkBundleClient->getContainer()->get('em');
    }

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
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
        self::$frameworkBundleClient->request('GET', '/v2/organisation/2', [], [], $this->headers);

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertTrue($responseContent['success']);
        $this->assertEquals(2, $responseContent['data']['id']);
        $this->assertEquals('Org 2', $responseContent['data']['name']);
        $this->assertEquals('org_email_2', $responseContent['data']['email_identifier']);
        $this->assertTrue($responseContent['data']['is_activated']);
    }

    /**
     * @test
     */
    public function getByIdActionReturns404IfNotFound()
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/27', [], [], $this->headers);

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
        self::$frameworkBundleClient->request('GET', '/v2/organisation/1', [], [], $this->headersDeputy);

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function getByIdActionAllowsDeputiesFetchTheirOwnOrganisation()
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/4', [], [], $this->headersDeputy);

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
     * @dataProvider getBadRequestData
     * @param $data
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

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function updateActionUpdatesAnOrganisation()
    {
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/1',
            [],
            [],
            $this->headers,
            '{"name": "Org Name Updated", "email_identifier": "email_id_updated", "is_activated": false}'
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $organisation = self::$em
            ->getRepository(Organisation::class)
            ->findOneBy(['id' => 1]);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Org Name Updated', $organisation->getName());
        $this->assertEquals('email_id_updated', $organisation->getEmailIdentifier());
        $this->assertFalse($organisation->IsActivated());
    }

    /**
     * @test
     * @dataProvider getBadRequestData
     * @param $data
     */
    public function updateActionReturnsBadRequestIfGivenBadData($data)
    {
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/2',
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
            ['data' => '{"name": "Org Name", "email_identifier": null, "is_activated": true}']
        ];
    }

    /**
     * @test
     */
    public function updateActionReturnsBadRequestIfGivenExistingEmailIdentifier()
    {
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/2',
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
        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/2',
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
            ->findOneBy(['id' => 2]);

        $this->assertNull($organisation);
    }

    /**
     * @test
     */
    public function adminsCannotDeleteOrganisation()
    {
        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/1',
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
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/1/user/3',
            [],
            [],
            $this->headers
        );

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $organisation = self::$em
            ->getRepository(Organisation::class)
            ->findOneBy(['id' => 1]);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals(1, count($organisation->getUsers()));
        $this->assertEquals(3, $organisation->getUsers()[0]->getId());
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
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/1/user/9003',
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
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/1/user/3',
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
        self::$frameworkBundleClient->request(
            'PUT',
            '/v2/organisation/4/user/3',
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
        self::fixtures()->addUserToOrganisation(5, 3);
        self::fixtures()->flush()->clear();

        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/3/user/5',
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
            ->findOneBy(['id' => 3]);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertTrue($organisation->getUsers()->isEmpty());
    }

    /**
     * @test
     */
    public function removeUserActionReturnsNotFoundOnInvalidOrganisationId()
    {
        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/9001/user/3',
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
        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/1/user/9003',
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
        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/1/user/3',
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
        self::$frameworkBundleClient->request(
            'DELETE',
            '/v2/organisation/4/user/3',
            [],
            [],
            $this->headersDeputy
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
