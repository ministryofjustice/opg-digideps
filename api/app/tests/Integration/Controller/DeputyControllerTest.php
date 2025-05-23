<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWT\JWTService;
use App\TestHelpers\CourtOrderTestHelper;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\Tests\Behat\v2\Helpers\FixtureHelper;
use App\Tests\Integration\Controller\JsonHttpTestClient;
use App\Tests\Integration\Fixtures;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeputyControllerTest extends WebTestCase
{
    private static JsonHttpTestClient $client;
    private static Fixtures $fixtures;
    private static EntityManager $em;
    private static FixtureHelper $fixtureHelper;
    private static string $deputySecret;

    public static function setUpBeforeClass(): void
    {
        $browser = static::createClient(['environment' => 'test', 'debug' => false]);
        $container = static::getContainer();

        /** @var JWTService $jwtService */
        $jwtService = $container->get('App\Service\JWT\JWTService');
        self::$client = new JsonHttpTestClient($browser, $jwtService);

        /** @var EntityManager $em */
        $em = $container->get('em');
        self::$em = $em;
        self::$fixtures = new Fixtures(self::$em);

        /** @var FixtureHelper $fixtureHelper */
        $fixtureHelper = $container->get(FixtureHelper::class);
        self::$fixtureHelper = $fixtureHelper;

        self::$deputySecret = getenv('SECRETS_FRONT_KEY');
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        self::$fixtures->clear();
    }

    public function testAddAuth()
    {
        $url = '/deputy/add';

        self::$client->assertEndpointNeedsAuth('POST', $url);
    }

    public function testAdd()
    {
        $firstName = 'Bill';
        $lastName = 'Baggins';
        $email = 'deputy@example.org';
        $deputyUid = '7999999990';
        $token = self::$client->login($email, 'DigidepsPass1234', self::$deputySecret);

        $return = self::$client->assertJsonRequest('POST', '/deputy/add', [
            'data' => [
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $email,
                'deputy_uid' => $deputyUid,
            ],
            'mustSucceed' => true,
            'AuthToken' => $token,
        ]);

        $deputy = self::$fixtures->clear()->getRepo('Deputy')->find($return['data']['id']);

        $this->assertEquals($firstName, $deputy->getFirstname());
        $this->assertEquals($lastName, $deputy->getLastname());
        $this->assertEquals($email, $deputy->getEmail1());
        $this->assertEquals($deputyUid, $deputy->getDeputyUid());
    }

    public function testAddDeputyIdAlreadyExists()
    {
        $firstName = 'Bill';
        $lastName = 'Baggins';
        $email = 'deputy@example.org';
        $deputyUid = '7999999990';
        $token = self::$client->login($email, 'DigidepsPass1234', self::$deputySecret);

        $return = self::$client->assertJsonRequest('POST', '/deputy/add', [
            'data' => [
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $email,
                'deputy_uid' => $deputyUid,
            ],
            'mustSucceed' => true,
            'AuthToken' => $token,
        ]);

        $deputy = self::$fixtures->clear()->getRepo('Deputy')->find($return['data']['id']);

        $this->assertEquals($firstName, $deputy->getFirstname());
        $this->assertEquals($lastName, $deputy->getLastname());
        $this->assertEquals($email, $deputy->getEmail1());
        $this->assertEquals($deputyUid, $deputy->getDeputyUid());
    }

    public function testDeputyReportUrlNeedsAuth()
    {
        self::$client->assertEndpointNeedsAuth('GET', '/v2/deputy/7999999990/reports');
    }

    public function testGetDeputyReportsNotFound()
    {
        $email = 'n.s@example.org';
        $user = self::$fixtures->createUser([
            'setEmail' => $email,
            'setRoleName' => User::ROLE_LAY_DEPUTY,
            'setDeputyUid' => '7099999990',
        ]);
        self::$fixtureHelper->setPassword($user);
        // login to get token
        $token = self::$client->login($email, 'DigidepsPass1234', self::$deputySecret);

        // make the API call
        self::$client->assertJsonRequest(
            'GET',
            '/v2/deputy/7000000000/reports',
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );

        self::$fixtures->remove($user)->flush()->clear();
    }

    public function testGetDeputyReportsEmptyResponse()
    {
        // setup required user for auth
        $email = 'n.s@example.org';
        $deputyUid = '7099999991';
        $user = self::$fixtures->createUser([
            'setEmail' => $email,
            'setRoleName' => User::ROLE_LAY_DEPUTY,
            'setDeputyUid' => $deputyUid,
        ]);
        self::$fixtureHelper->setPassword($user);

        // login to get token
        $token = self::$client->login($email, 'DigidepsPass1234', self::$deputySecret);

        // Make API call
        $responseJson = self::$client->assertJsonRequest(
            'GET',
            "/v2/deputy/{$deputyUid}/reports",
            ['AuthToken' => $token, 'mustSucceed' => true]
        );

        $this->assertCount(0, $responseJson['data']);

        self::$fixtures->remove($user)->flush()->clear();
    }

    public function testGetDeputyReportsReturnsResults()
    {
        $email = 'n.s@example.org';
        $deputyUid = '7044444440';

        // create user
        $user = self::$fixtures->createUser([
            'setEmail' => $email,
            'setRoleName' => User::ROLE_LAY_DEPUTY,
            'setDeputyUid' => $deputyUid,
        ]);
        self::$fixtureHelper->setPassword($user);

        // generate deputy and set user
        $deputy = DeputyTestHelper::generateDeputy($email, $deputyUid, $user);
        self::$fixtures->persist($deputy);
        self::$fixtures->flush();

        // generate client and set deputy
        $client = self::$fixtures->createClient($user);
        $client->setDeputy($deputy);
        self::$fixtures->persist($client);
        self::$fixtures->flush();

        // generate courtOrder and set client and deputy
        $courtOrder = self::$fixtures->createCourtOrder('7055555550', 'pfa', true);
        $courtOrder->setClient($client);
        $courtOrderDeputy = CourtOrderTestHelper::associateDeputyToCourtOrder(self::$em, $courtOrder, $deputy);
        self::$fixtures->persist($courtOrder);
        self::$fixtures->flush();

        // generate and add a report to the court order
        $startDate = new \DateTime();
        $report = ReportTestHelper::generateReport(self::$em, $client, startDate: $startDate);
        $courtOrder->addReport($report);
        self::$fixtures->persist($courtOrder);
        self::$fixtures->flush();

        // login to get token
        $token = self::$client->login($email, 'DigidepsPass1234', self::$deputySecret);

        // Make API call
        $responseJson = self::$client->assertJsonRequest(
            'GET',
            "/v2/deputy/{$deputyUid}/reports",
            ['AuthToken' => $token, 'mustSucceed' => true]
        );

        self::assertCount(1, $responseJson['data']);

        self::$fixtures->remove($courtOrderDeputy, $deputy, $user)->flush()->clear();
    }
}
