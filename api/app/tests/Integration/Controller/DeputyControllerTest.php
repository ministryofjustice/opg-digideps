<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWT\JWTService;
use App\TestHelpers\CourtOrderTestHelper;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\Tests\Behat\v2\Helpers\FixtureHelper;
use App\Tests\Integration\Controller\AbstractTestController;
use App\Tests\Integration\Controller\JsonHttpTestClient;

class DeputyControllerTest extends AbstractTestController
{
    private static ?string $tokenAdmin = null;
    private static ?string $tokenDeputy = null;
    private static FixtureHelper $fixtureHelper;
    private static JsonHttpTestClient $client;
    

    public function setUp(): void
    {
        parent::setUp();

        $container = static::getContainer();

        /** @var JWTService $jwtService */
        $jwtService = $container->get('App\Service\JWT\JWTService');
        self::$client = new JsonHttpTestClient(self::$frameworkBundleClient, $this->jwtService);

        /** @var FixtureHelper $fixtureHelper */
        $fixtureHelper = $container->get(FixtureHelper::class);
        self::$fixtureHelper = $fixtureHelper;
        
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
     */
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
        self::$tokenDeputy = $this->loginAsDeputy();

        $return = $this->assertJsonRequest('POST', '/deputy/add', [
            'data' => [
                'firstname' => 'n',
                'lastname' => 's',
                'email' => 'n.s@example.org',
                'deputy_uid' => '7999999990',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $deputy = $this->fixtures()->clear()->getRepo('Deputy')->find($return['data']['id']);

        $this->assertEquals('n', $deputy->getFirstname());
        $this->assertEquals('s', $deputy->getLastname());
        $this->assertEquals('n.s@example.org', $deputy->getEmail1());
        $this->assertEquals('7999999990', $deputy->getDeputyUid());
    }

    public function testAddDeputyIdAlreadyExists()
    {
        self::$tokenDeputy = $this->loginAsDeputy();

        $return = $this->assertJsonRequest('POST', '/deputy/add', [
            'data' => [
                'firstname' => 'd',
                'lastname' => 'e',
                'email' => 'd.e@example.org',
                'deputy_uid' => '7999999990',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $deputy = $this->fixtures()->clear()->getRepo('Deputy')->find($return['data']['id']);

        $this->assertEquals('n', $deputy->getFirstname());
        $this->assertEquals('s', $deputy->getLastname());
        $this->assertEquals('n.s@example.org', $deputy->getEmail1());
        $this->assertEquals('7999999990', $deputy->getDeputyUid());
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
        //login to get token
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
        
        //login to get token
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
        $deputy->setUser($user);
        self::$fixtures->persist($deputy);
        self::$fixtures->flush();

        // generate client and set deputy
        $client = self::$fixtures->createClient($user);
        $client->setDeputy($deputy);
        self::$fixtures->persist($client);
        self::$fixtures->flush();

        // generate courtOrder and set client and deputy
        $courtOrder = self::$fixtures->createCourtOrder(7055555550, 'pfa', true);
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

        //login to get token
        $token = self::$client->login($email, 'DigidepsPass1234', self::$deputySecret);

        // Make API call
        $responseJson = self::$client->assertJsonRequest(
            'GET',
            "/v2/deputy/{$deputyUid}/reports",
            ['AuthToken' => $token, 'mustSucceed' => true]
        );
        
        self::assertCount(1, $responseJson['data']);

        self::$fixtures->remove($user)->flush()->clear();
    }
}
