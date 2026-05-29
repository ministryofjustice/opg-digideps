<?php

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Deputy;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Fixture\UserType;

class DeputyControllerTest extends AbstractTestController
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::$fixtures->clear();
    }

    public function testAddAuth()
    {
        $url = '/deputy/add';

        self::assertEndpointNeedsAuth('POST', $url);
    }

    public function testAdd()
    {
        $firstName = 'Bill';
        $lastName = 'Baggins';
        $email = 'deputy@example.org';
        $deputyUid = '7999999990';
        $token = self::login($email, 'DigidepsPass1234', self::$deputySecret);

        $return = self::assertJsonRequest('POST', '/deputy/add', [
            'data' => [
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $email,
                'deputy_uid' => $deputyUid,
                'role_name' => 'ROLE_LAY_DEPUTY',
            ],
            'mustSucceed' => true,
            'AuthToken' => $token,
        ]);

        $deputy = self::$fixtures->clear()->getRepo(Deputy::class)->find($return['data']['id']);

        $this->assertNotNull($deputy);
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
        $token = self::login($email, 'DigidepsPass1234', self::$deputySecret);

        $return = self::assertJsonRequest('POST', '/deputy/add', [
            'data' => [
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $email,
                'deputy_uid' => $deputyUid,
                'role_name' => 'ROLE_LAY_DEPUTY'
            ],
            'mustSucceed' => true,
            'AuthToken' => $token,
        ]);

        $deputy = self::$fixtures->clear()->getRepo(Deputy::class)->find($return['data']['id']);

        $this->assertNotNull($deputy);
        $this->assertEquals($firstName, $deputy->getFirstname());
        $this->assertEquals($lastName, $deputy->getLastname());
        $this->assertEquals($email, $deputy->getEmail1());
        $this->assertEquals($deputyUid, $deputy->getDeputyUid());
    }

    public function testDeputyReportUrlNeedsAuth()
    {
        self::assertEndpointNeedsAuth('GET', '/v2/deputy/7999999990/courtorders');
    }

    public function testGetDeputyReportsNotFound()
    {
        $user = self::$fixtureService->instantiateOnlyUser(UserType::Deputy, DeputyType::LAY);

        // login to get token
        $token = self::loginAsDeputy($user->getEmail());
        $uid = self::$fixtureService->getUid();

        // make the API call
        self::assertJsonRequest(
            'GET',
            "/v2/deputy/{$uid}/courtorders",
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetDeputyReportsEmptyResponse()
    {
        $user = self::$fixtureService->instantiateOnlyUser(UserType::Deputy, DeputyType::LAY);
        // login to get token
        $token = self::loginAsDeputy($user->getEmail());

        // Make API call
        $responseJson = self::assertJsonRequest(
            'GET',
            "/v2/deputy/{$user->getDeputyUid()}/courtorders",
            ['AuthToken' => $token, 'mustSucceed' => true]
        );

        $this->assertCount(0, $responseJson['data']);
    }

    public function testGetDeputyReportsReturnsResults()
    {
        ['persons' => ['users' => ['lay1' => $user]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        // login to get token
        $token = self::loginAsDeputy($user->getEmail());

        // Make API call
        $responseJson = self::assertJsonRequest(
            'GET',
            "/v2/deputy/{$user->getDeputyUid()}/courtorders",
            ['AuthToken' => $token, 'mustSucceed' => true]
        );

        self::assertCount(1, $responseJson['data']);
    }
}
