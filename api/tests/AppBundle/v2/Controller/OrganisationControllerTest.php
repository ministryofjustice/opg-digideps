<?php

namespace Tests\AppBundle\v2\Controller;

use AppBundle\Entity\Organisation;
use Doctrine\ORM\EntityManager;
use Tests\AppBundle\Controller\AbstractTestController;

class OrganisationControllerTest extends AbstractTestController
{
    /** @var array */
    private $headers = [];

    /** @var EntityManager */
    private static $em;

    /** @var null"string */
    private static $tokenAdmin = null;

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::fixtures()->createOrganisations(3);
        self::fixtures()->flush()->clear();

        self::$em = self::$frameworkBundleClient->getContainer()->get('em');
    }

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
        }

        $this->headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenAdmin];
    }

    /**
     * @test
     */
    public function getAllActionReturnsAllOrganisations()
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/list', [], [], $this->headers);

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertTrue($responseContent['success']);
        $this->assertCount(3, $responseContent['data']);
    }

    /**
     * @test
     */
    public function getByIdActionReturnsOrganisationsIfFound()
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/2', [], [], $this->headers);

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
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
    public function getByIdActionReturnsEmptyIfNotFound()
    {
        self::$frameworkBundleClient->request('GET', '/v2/organisation/27', [], [], $this->headers);

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertFalse($responseContent['success']);
        $this->assertEquals('Organisation id: 27 not found', $responseContent['message']);
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

        $this->assertEquals(201, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(204, $response->getStatusCode());

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
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @return array
     */
    public function getBadRequestData()
    {
        return [
            ['data' => '{"name": "Org Name", "email_identifier": "unique_id"}'],
            ['data' => '{"name": "Org Name", "is_activated": true}'],
            ['data' => '{"email_identifier": "unique_id", "is_activated": true}'],
            ['data' => '{"name": "", "email_identifier": "unique_id", "is_activated": "true"}'],
            ['data' => '{"name": "Org Name", "email_identifier": "", "is_activated": true}'],
            ['data' => '{"name": null, "email_identifier": "unique_id", "is_activated": true}'],
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
        $this->assertEquals(400, $response->getStatusCode());
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
            $this->headers
        );

        $response = self::$frameworkBundleClient->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertTrue($responseContent['success']);
        $this->assertEquals('Organisation deleted', $responseContent['message']);

        $organisation = self::$em
            ->getRepository(Organisation::class)
            ->findOneBy(['id' => 2]);

        $this->assertNull($organisation);
    }
}
