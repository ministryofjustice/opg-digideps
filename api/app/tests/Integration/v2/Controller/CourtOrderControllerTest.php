<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Controller;

use DateTime;
use App\Entity\Deputy;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\TestHelpers\ReportTestHelper;
use App\Tests\Behat\v2\Helpers\FixtureHelper;
use App\Tests\Integration\Controller\AbstractTestController;
use App\Tests\Integration\Controller\JsonHttpTestClient;

class CourtOrderControllerTest extends AbstractTestController
{
    private static JsonHttpTestClient $client;
    private static FixtureHelper $fixtureHelper;
    private static ReportTestHelper $reportTestHelper;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $container = static::getContainer();

        self::$fixtureHelper = $container->get(FixtureHelper::class);
        self::$reportTestHelper = ReportTestHelper::create();
        self::$client = new JsonHttpTestClient(self::$frameworkBundleClient, self::$jwtService);
    }

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

    // returns User $user (user for $deputy), CourtOrder $courtOrder (associated with $deputy), Deputy $deputy
    private function addUserAndCourtOrderAndDeputy($emailAddress): array
    {
        // add a court order, and make the user a deputy on it
        $courtOrder = self::$fixtures->createCourtOrder(substr(''.hexdec(uniqid()), -8), 'pfa', 'ACTIVE');

        $user = self::$fixtures->createUser($emailAddress, User::ROLE_LAY_DEPUTY);
        self::$fixtureHelper->setPassword($user);

        // associate deputy with court order
        $deputy = $this->createDeputyForUser($user);
        $deputy->associateWithCourtOrder($courtOrder);

        self::$fixtures->persist($courtOrder, $user, $deputy)->flush();

        return [$user, $courtOrder];
    }

    public function testGetByUidActionNoAuthFail()
    {
        self::$client->assertEndpointNeedsAuth('GET', '/v2/courtorder/71101111');
    }

    public function testGetByUidActionCourtOrderNotFoundFail(): void
    {
        $user = self::$fixtures->createUser('fail-not-found-court-order-test@opg.gov.uk', User::ROLE_LAY_DEPUTY);
        self::$fixtureHelper->setPassword($user);

        // log in and fetch court order which doesn't exist
        $token = self::$client->login('fail-not-found-court-order-test@opg.gov.uk', 'DigidepsPass1234', self::$deputySecret);

        // make the API call
        self::$client->assertJsonRequest(
            'GET',
            '/v2/courtorder/9292777777',
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetByUidActionUserIsNotADeputyFail(): void
    {
        // add a court order
        $courtOrder = self::$fixtures->createCourtOrder('92954529292', 'hw', 'ACTIVE');
        self::$fixtures->persist($courtOrder);
        self::$fixtures->flush();

        // log in, and fetch court order which exists, but user has no deputy record
        $user = self::$fixtures->createUser('fail-user-not-deputy-court-order-test@opg.gov.uk', User::ROLE_LAY_DEPUTY);
        self::$fixtureHelper->setPassword($user);

        $token = self::$client->login('fail-user-not-deputy-court-order-test@opg.gov.uk', 'DigidepsPass1234', self::$deputySecret);

        self::$client->assertJsonRequest(
            'GET',
            '/v2/courtorder/92954529292',
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetByUidActionUserIsNotADeputyOnCourtOrderFail(): void
    {
        // add a court order
        $courtOrder = self::$fixtures->createCourtOrder('9292929292', 'hw', 'ACTIVE');
        self::$fixtures->persist($courtOrder);
        self::$fixtures->flush();

        // create a deputy for the user, so they have a valid deputy record, but don't associate with court order
        $user = self::$fixtures->createUser('fail-not-deputy-on-court-order-test@opg.gov.uk', User::ROLE_LAY_DEPUTY);
        self::$fixtureHelper->setPassword($user);
        $this->createDeputyForUser($user);

        // log in, and fetch court order which exists but for which the logged-in user is not a deputy
        $token = self::$client->login('fail-not-deputy-on-court-order-test@opg.gov.uk', 'DigidepsPass1234', self::$deputySecret);

        self::$client->assertJsonRequest(
            'GET',
            '/v2/courtorder/9292929292',
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetByUidActionSuccess(): void
    {
        [$user, $courtOrder] = $this->addUserAndCourtOrderAndDeputy('successful-court-order-test@opg.gov.uk');

        // client
        $client = self::$fixtures->createClient($user);
        self::$fixtures->persist($client)->flush();

        // add an unsubmitted (current) report to the court order
        $startDate = new DateTime();
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
                $actualSubmitDate = new DateTime($report['submit_date']);
                $this->assertEquals($submitDate->format(DateTime::ATOM), $actualSubmitDate->format(DateTime::ATOM));
                $this->assertNull($report['un_submit_date']);
            } else {
                // current report => no submit date or unsubmit date
                $this->assertNull($report['submit_date']);
                $this->assertNull($report['un_submit_date']);
            }
        }
    }

    public function testInviteDeputyActionNoAuthFail()
    {
        $courtOrderUid = '7747628917';

        $courtOrder = self::$fixtures->createCourtOrder($courtOrderUid, 'pfa', 'ACTIVE');
        self::$fixtures->persist($courtOrder)->flush();

        self::$client->assertEndpointNeedsAuth('POST', "/v2/courtorder/$courtOrderUid/lay-deputy-invite");
    }

    public function testInviteLayDeputyActionInvalidPayloadFail()
    {
        [$user, $courtOrder] = $this->addUserAndCourtOrderAndDeputy('court-order-invite-deputy-payload-test@opg.gov.uk');

        // login to get the token for API calls
        $token = self::$client->login($user->getEmail(), 'DigidepsPass1234', self::$deputySecret);

        // make the API call to associate a new co-deputy with the court order
        $postData = [
            'firstname' => 'Lobo',
            'role_name' => User::ROLE_LAY_DEPUTY,
        ];

        $responseJson = self::$client->assertJsonRequest(
            'POST',
            "/v2/courtorder/{$courtOrder->getCourtOrderUid()}/lay-deputy-invite",
            ['AuthToken' => $token, 'mustFail' => true, 'data' => $postData]
        );

        self::assertStringContainsString('invalid invitee details', $responseJson['message']);
    }

    public function testInviteLayDeputyActionSuccess()
    {
        // $deputy has access to court order; $user is the User entity for $invitingDeputy
        [$user, $courtOrder] = $this->addUserAndCourtOrderAndDeputy('court-order-invite-deputy-test@opg.gov.uk');

        // client
        $client = self::$fixtures->createClient(settersMap: ['setCaseNumber' => '1122334455']);
        $courtOrder->setClient($client);
        self::$fixtures->persist($courtOrder)->flush();

        // add pre-reg record for deputy to be invited
        $invitedDeputy = new PreRegistration([
            'Case' => $client->getCaseNumber(),
            'DeputyFirstname' => 'Amz',
            'DeputySurname' => 'Sloroz',
            'DeputyUid' => '123415235',
        ]);
        self::$fixtures->persist($invitedDeputy)->flush();

        // login to get the token for API calls
        $token = self::$client->login($user->getEmail(), 'DigidepsPass1234', self::$deputySecret);

        // make the API call to associate a new co-deputy with the court order
        $postData = [
            'email' => 'successful-court-order-invite-test@opg.gov.uk',
            'firstname' => $invitedDeputy->getDeputyFirstname(),
            'lastname' => $invitedDeputy->getDeputySurname(),
            'role_name' => User::ROLE_LAY_DEPUTY,
        ];

        $responseJson = self::$client->assertJsonRequest(
            'POST',
            "/v2/courtorder/{$courtOrder->getCourtOrderUid()}/lay-deputy-invite",
            ['AuthToken' => $token, 'mustSucceed' => true, 'data' => $postData]
        );

        // assertions
        self::assertEquals(true, $responseJson['success']);
        self::assertMatchesRegularExpression('/[0-9a-z]{40}/', $responseJson['data']['registrationToken']);
    }
}
