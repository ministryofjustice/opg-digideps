<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Controller;

use App\Entity\Deputy;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Service\JWT\JWTService;
use App\TestHelpers\ReportTestHelper;
use App\Tests\Behat\v2\Helpers\FixtureHelper;
use App\Tests\Integration\Controller\JsonHttpTestClient;
use App\Tests\Integration\Fixtures;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CourtOrderControllerTest extends WebTestCase
{
    private static JsonHttpTestClient $client;
    private static Fixtures $fixtures;
    private static EntityManager $em;
    private static FixtureHelper $fixtureHelper;
    private static ReportTestHelper $reportTestHelper;
    private static string $deputySecret;

    private function createDeputyForUser(User $user): Deputy
    {
        $deputy = new Deputy();
        $deputy->setEmail1($user->getEmail());
        $deputy->setDeputyUid('748723'.rand(1, 99999));
        $deputy->setFirstname('name'.time());
        $deputy->setLastname('surname'.time());

        $deputy->setUser($user);

        self::$fixtures->persist($deputy);
        self::$fixtures->flush();

        return $deputy;
    }

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

        self::$reportTestHelper = new ReportTestHelper();

        self::$deputySecret = getenv('SECRETS_FRONT_KEY');
    }

    // returns $user (user for $deputy), $courtOrder (associated with $deputy), $deputy
    private function addUserAndCourtOrderAndDeputy($emailAddress): array
    {
        // add a court order, and make the user a deputy on it
        $courtOrder = self::$fixtures->createCourtOrder('7747728317', 'pfa', 'ACTIVE');
        self::$fixtures->persist($courtOrder);
        self::$fixtures->flush();

        $user = self::$fixtures->createUser([
            'setEmail' => $emailAddress,
            'setRoleName' => User::ROLE_LAY_DEPUTY,
        ]);
        self::$fixtureHelper->setPassword($user);

        // associate deputy with court order
        $deputy = $this->createDeputyForUser($user);
        $deputy->associateWithCourtOrder($courtOrder);

        self::$fixtures->persist($deputy)->flush();

        return [$user, $courtOrder, $deputy];
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
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );

        self::$fixtures->remove($user)->flush()->clear();
    }

    public function testGetByUidActionUserIsNotADeputyFail(): void
    {
        // add a court order
        $courtOrder = self::$fixtures->createCourtOrder('92954529292', 'hw', 'ACTIVE');
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
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );

        self::$fixtures->remove($user)->flush()->clear();
    }

    public function testGetByUidActionUserIsNotADeputyOnCourtOrderFail(): void
    {
        // add a court order
        $courtOrder = self::$fixtures->createCourtOrder('9292929292', 'hw', 'ACTIVE');
        self::$fixtures->persist($courtOrder);
        self::$fixtures->flush();

        // create a deputy for the user, so they have a valid deputy record, but don't associate with court order
        $user = self::$fixtures->createUser([
            'setEmail' => 'fail-not-deputy-on-court-order-test@opg.gov.uk',
            'setRoleName' => User::ROLE_LAY_DEPUTY,
        ]);
        self::$fixtureHelper->setPassword($user);
        $deputy = $this->createDeputyForUser($user);

        // log in, and fetch court order which exists but for which the logged-in user is not a deputy
        $token = self::$client->login('fail-not-deputy-on-court-order-test@opg.gov.uk', 'DigidepsPass1234', self::$deputySecret);

        self::$client->assertJsonRequest(
            'GET',
            '/v2/courtorder/9292929292',
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );

        self::$fixtures->remove($user, $deputy)->flush()->clear();
    }

    public function testGetByUidActionSuccess(): void
    {
        [$user, $courtOrder, $deputy] = $this->addUserAndCourtOrderAndDeputy('successful-court-order-test@opg.gov.uk');

        // client
        $client = self::$fixtures->createClient($user);
        self::$fixtures->persist($client)->flush();

        // add an unsubmitted (current) report to the court order
        $startDate = new \DateTime();
        $report1 = self::$reportTestHelper->generateReport(self::$em, client: $client, startDate: $startDate);
        $courtOrder->addReport($report1);
        self::$fixtures->persist($courtOrder)->flush();

        // add a submitted report to the court order
        $previousReportStartDate = $startDate->modify('-365 days');
        $submitDate = $previousReportStartDate->modify('+30 days');
        $report2 = self::$reportTestHelper->generateReport(self::$em, client: $client, startDate: $previousReportStartDate, dateChecks: false);
        $courtOrder->addReport($report2);
        self::$reportTestHelper->submitReport($report2, self::$em, submittedBy: $user, submitDate: $submitDate);
        self::$fixtures->persist($courtOrder)->flush();

        // login to get the token for API calls
        $token = self::$client->login('successful-court-order-test@opg.gov.uk', 'DigidepsPass1234', self::$deputySecret);

        // make the API call
        $responseJson = self::$client->assertJsonRequest(
            'GET',
            "/v2/courtorder/{$courtOrder->getCourtOrderUid()}",
            ['AuthToken' => $token, 'mustSucceed' => true]
        );

        // assertions
        $this->assertCount(2, $responseJson['data']['reports']);

        // check that data we need for determining the unsubmitted (current) report is available
        foreach ($responseJson['data']['reports'] as $report) {
            if ($report['submitted']) {
                // previous submitted report
                $actualSubmitDate = new \DateTime($report['submit_date']);
                $this->assertEquals($submitDate->format(\DateTime::ATOM), $actualSubmitDate->format(\DateTime::ATOM));
                $this->assertNull($report['un_submit_date']);
            } else {
                // current report => no submit date or unsubmit date
                $this->assertNull($report['submit_date']);
                $this->assertNull($report['un_submit_date']);
            }
        }

        // clean up
        self::$fixtures->remove($user, $deputy, $courtOrder)->flush()->clear();
    }

    public function testInviteDeputyActionNoAuthFail()
    {
        self::$client->assertEndpointNeedsAuth('GET', '/v2/courtorder/94929596/invite');
    }

    public function testInviteDeputyActionInvalidPayload()
    {
    }

    public function testInviteDeputyActionSuccess()
    {
        // $deputy has access to court order; $user is the User entity for $deputy
        [$user, $courtOrder, $deputy] = $this->addUserAndCourtOrderAndDeputy('successful-court-order-invite-test@opg.gov.uk');

        // add pre-reg record for deputy to be invited
        $invitedDeputy = new PreRegistration();
        $invitedDeputy->setCaseNumber();
        $invitedDeputy->

        // login to get the token for API calls
        $token = self::$client->login($user->getEmail(), 'DigidepsPass1234', self::$deputySecret);

        // make the API call to associate the second deputy with the court order
        $postData = [
            'email' => $email,
            $data['firstname'] ?? '',
            $data['lastname'] ?? '',
            $data['role_name'] ?? User::ROLE_LAY_DEPUTY,
        ];

        $responseJson = self::$client->assertJsonRequest(
            'POST',
            "/v2/courtorder/{$courtOrder->getCourtOrderUid()}/invite",
            ['AuthToken' => $token, 'mustSucceed' => true, 'data' => $postData]
        );
    }
}
