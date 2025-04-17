<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Controller;

use App\Entity\Deputy;
use App\Entity\User;
use App\Tests\Behat\v2\Helpers\FixtureHelper;
use App\Tests\Integration\Controller\JsonHttpTestClient;
use App\Tests\Integration\Fixtures;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CourtOrderControllerTest extends WebTestCase
{
    private static JsonHttpTestClient $client;
    private static Fixtures $fixtures;
    private static FixtureHelper $fixtureHelper;
    private static string $deputySecret;

    public static function setUpBeforeClass(): void
    {
        self::$client = new JsonHttpTestClient(static::createClient(['environment' => 'test', 'debug' => false]));

        $container = static::getContainer();

        /** @var EntityManager $em */
        $em = $container->get('em');
        self::$fixtures = new Fixtures($em);

        /** @var FixtureHelper $fixtureHelper */
        $fixtureHelper = $container->get(FixtureHelper::class);
        self::$fixtureHelper = $fixtureHelper;

        self::$deputySecret = getenv('SECRETS_FRONT_KEY');
    }

    private function createDeputyForUser(User $user): Deputy
    {
        $deputy = new Deputy();
        $deputy->setEmail1($user->getEmail());
        $deputy->setDeputyUid(748723 + rand(1, 99999));
        $deputy->setFirstname('name'.time());
        $deputy->setLastname('surname'.time());

        $deputy->setUser($user);

        self::$fixtures->persist($deputy);
        self::$fixtures->flush();

        return $deputy;
    }

    public function testGetByUidActionNoAuthFail()
    {
        self::$client->assertEndpointNeedsAuth('GET', '/v2/courtorder/71101111');
    }

    public function testGetByUidActionCourtOrderNotFoundFail(): void
    {
        $user = self::$fixtures->createUser([
            'setEmail' => 'fail-not-found-court-order-test@opg.gov.uk',
            'setRoleName' => User::ROLE_LAY_DEPUTY,
        ]);
        self::$fixtureHelper->setPassword($user);

        // log in and fetch court order which doesn't exist
        $token = self::$client->login('fail-not-found-court-order-test@opg.gov.uk', 'DigidepsPass1234', self::$deputySecret);

        // make the API call
        self::$client->assertJsonRequest(
            'GET',
            '/v2/courtorder/9292777777',
            ['AuthToken' => $token, 'mustFail' => true, 'assertCode' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetByUidActionUserIsNotADeputyFail(): void
    {
        // add a court order
        $courtOrder = self::$fixtures->createCourtOrder(92954529292, 'hw', true);
        self::$fixtures->persist($courtOrder);
        self::$fixtures->flush();

        // log in, and fetch court order which exists, but user has no deputy record
        $user = self::$fixtures->createUser([
            'setEmail' => 'fail-user-not-deputy-court-order-test@opg.gov.uk',
            'setRoleName' => User::ROLE_LAY_DEPUTY,
        ]);
        self::$fixtureHelper->setPassword($user);

        $token = self::$client->login('fail-user-not-deputy-court-order-test@opg.gov.uk', 'DigidepsPass1234', self::$deputySecret);

        self::$client->assertJsonRequest(
            'GET',
            '/v2/courtorder/92954529292',
            ['AuthToken' => $token, 'mustFail' => true, 'assertCode' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetByUidActionUserIsNotADeputyOnCourtOrderFail(): void
    {
        // add a court order
        $courtOrder = self::$fixtures->createCourtOrder(9292929292, 'hw', true);
        self::$fixtures->persist($courtOrder);
        self::$fixtures->flush();

        // create a deputy for the user, so they have a valid deputy record, but don't associate with court order
        $user = self::$fixtures->createUser([
            'setEmail' => 'fail-not-deputy-on-court-order-test@opg.gov.uk',
            'setRoleName' => User::ROLE_LAY_DEPUTY,
        ]);
        self::$fixtureHelper->setPassword($user);
        $this->createDeputyForUser($user);

        // log in, and fetch court order which exists but for which the logged-in user is not a deputy
        $token = self::$client->login('fail-not-deputy-on-court-order-test@opg.gov.uk', 'DigidepsPass1234', self::$deputySecret);

        self::$client->assertJsonRequest(
            'GET',
            '/v2/courtorder/9292929292',
            ['AuthToken' => $token, 'mustFail' => true, 'assertCode' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetByUidActionSuccess(): void
    {
        // add a court order, and make the user a deputy on it
        $courtOrder = self::$fixtures->createCourtOrder(7747728317, 'pfa', true);
        self::$fixtures->persist($courtOrder);
        self::$fixtures->flush();

        $user = self::$fixtures->createUser([
            'setEmail' => 'successful-court-order-test@opg.gov.uk',
            'setRoleName' => User::ROLE_LAY_DEPUTY,
        ]);
        self::$fixtureHelper->setPassword($user);

        // associate deputy with court order
        $deputy = $this->createDeputyForUser($user);
        $deputy->associateWithCourtOrder($courtOrder);

        self::$fixtures->persist($deputy);
        self::$fixtures->flush();

        // login to get the token for API calls
        $token = self::$client->login('successful-court-order-test@opg.gov.uk', 'DigidepsPass1234', self::$deputySecret);

        // make the API call
        self::$client->assertJsonRequest(
            'GET',
            "/v2/courtorder/{$courtOrder->getCourtOrderUid()}",
            ['AuthToken' => $token, 'mustSucceed' => true]
        );
    }
}
