<?php

namespace App\Tests\Integration\Controller;

use App\Entity\User;
use App\TestHelpers\CourtOrderTestHelper;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\Tests\Behat\v2\Helpers\FixtureHelper;

class DeputyControllerTest extends AbstractTestController
{
    private static JsonHttpTestClient $client;
    private static FixtureHelper $fixtureHelper;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $container = static::getContainer();

        self::$client = new JsonHttpTestClient(self::$frameworkBundleClient, self::$jwtService);
        self::$fixtureHelper = $container->get(FixtureHelper::class);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

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
        self::$client->assertEndpointNeedsAuth('GET', '/v2/deputy/7999999990/courtorders');
    }

    public function testGetDeputyReportsNotFound()
    {
        $email = 'n.s1@example.org';
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
            '/v2/deputy/7000000000/courtorders',
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetDeputyReportsEmptyResponse()
    {
        // setup required user for auth
        $email = 'n.s2@example.org';
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
            "/v2/deputy/{$deputyUid}/courtorders",
            ['AuthToken' => $token, 'mustSucceed' => true]
        );

        $this->assertCount(0, $responseJson['data']);
    }

    public function testGetDeputyReportsReturnsResults()
    {
        $email = 'n.s3@example.org';
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
        $courtOrder = self::$fixtures->createCourtOrder('7055555550', 'pfa', 'ACTIVE');
        $courtOrder->setClient($client);
        CourtOrderTestHelper::associateDeputyToCourtOrder(self::$em, $courtOrder, $deputy);
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
            "/v2/deputy/{$deputyUid}/courtorders",
            ['AuthToken' => $token, 'mustSucceed' => true]
        );

        self::assertCount(1, $responseJson['data']);
    }
}
