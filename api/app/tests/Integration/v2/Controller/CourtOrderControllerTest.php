<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\v2\Controller;

use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Entity\User;
use Tests\OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use Tests\OPG\Digideps\Backend\Fixture\DeputySet;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Fixture\UserType;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class CourtOrderControllerTest extends AbstractTestController
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    public function testGetByUidActionNoAuthFail()
    {
        self::assertEndpointNeedsAuth('GET', '/v2/courtorder/71101111');
    }

    public function testGetByUidActionCourtOrderNotFoundFail(): void
    {
        $user = self::$fixtureService->instantiateOnlyUser(UserType::Deputy, DeputyType::LAY);

        // log in and fetch court order which doesn't exist
        $token = self::loginAsDeputy($user->getEmail());

        // make the API call
        self::assertJsonRequest(
            'GET',
            '/v2/courtorder/9292777777',
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetByUidActionUserIsNotADeputyFail(): void
    {
        // add a court order
        ['orders' => [['hw' => ['order' => $courtOrder]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario(reportType: CourtOrderReportType::OPG104));

        // log in, and fetch court order which exists, but user has no deputy record
        $user = self::$fixtureService->instantiateOnlyUser(UserType::Deputy, DeputyType::LAY);

        $token = self::loginAsDeputy($user->getEmail());

        self::assertJsonRequest(
            'GET',
            "/v2/courtorder/{$courtOrder->getId()}",
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetByUidActionUserIsNotADeputyOnCourtOrderFail(): void
    {
        // add a court order
        ['orders' => [['hw' => ['order' => $courtOrder]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario(reportType: CourtOrderReportType::OPG104));

        // create a deputy for the user, so they have a valid deputy record, but don't associate with court order
        ['persons' => ['users' => ['lay1' => $user]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        // log in, and fetch court order which exists but for which the logged-in user is not a deputy
        $token = self::loginAsDeputy($user->getEmail());

        self::assertJsonRequest(
            'GET',
            "/v2/courtorder/{$courtOrder->getId()}",
            ['AuthToken' => $token, 'mustFail' => true, 'assertResponseCode' => 404]
        );
    }

    public function testGetByUidActionSuccess(): void
    {
        ['persons' => ['users' => ['lay1' => $user]], 'orders' => [['hw' => ['order' => $courtOrder, 'reports' => [$submitted, $open]]]]] = self::$fixtureService->instantiateScenario(
            new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), CourtOrderReportType::OPG104, 1))
        );

        // login to get the token for API calls
        $token = self::loginAsDeputy($user->getEmail());

        // make the API call
        $responseJson = self::assertJsonRequest(
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
                $this->assertEquals($submitted->getSubmitDate()?->format(\DateTime::ATOM), $actualSubmitDate->format(\DateTime::ATOM));
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
        self::assertEndpointNeedsAuth('POST', "/v2/courtorder/$courtOrderUid/lay-deputy-invite");
    }

    public function testInviteLayDeputyActionInvalidPayloadFail()
    {
        ['persons' => ['users' => ['lay1' => $user]], 'orders' => [['hw' => ['order' => $courtOrder]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario(reportType: CourtOrderReportType::OPG104));

        // login to get the token for API calls
        $token = self::loginAsDeputy($user->getEmail());

        // make the API call to associate a new co-deputy with the court order
        $postData = [
            'firstname' => 'Lobo',
            'role_name' => User::ROLE_LAY_DEPUTY,
        ];

        $responseJson = self::assertJsonRequest(
            'POST',
            "/v2/courtorder/{$courtOrder->getCourtOrderUid()}/lay-deputy-invite",
            ['AuthToken' => $token, 'mustFail' => true, 'data' => $postData]
        );

        self::assertStringContainsString('invalid invitee details', $responseJson['message']);
    }

    public function testInviteLayDeputyActionSuccess()
    {
        // $deputy has access to court order; $user is the User entity for $invitingDeputy
        ['client' => $client, 'persons' => ['users' => ['lay1' => $user]], 'orders' => [['hw' => ['order' => $courtOrder]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario(reportType: CourtOrderReportType::OPG104));

        // add pre-reg record for deputy to be invited
        $invitedDeputy = self::$fixtureService->persist(new PreRegistration([
            'Case' => $client->getCaseNumber(),
            'DeputyFirstname' => 'Amz',
            'DeputySurname' => 'Sloroz',
            'DeputyUid' => self::$fixtureService->getUid(),
        ]));
        self::$fixtureService->flush();

        // login to get the token for API calls
        $token = self::loginAsDeputy($user->getEmail());

        // make the API call to associate a new co-deputy with the court order
        $postData = [
            'email' => 'successful-court-order-invite-test@opg.gov.uk',
            'firstname' => $invitedDeputy->getDeputyFirstname(),
            'lastname' => $invitedDeputy->getDeputySurname(),
            'role_name' => User::ROLE_LAY_DEPUTY,
        ];

        $responseJson = self::assertJsonRequest(
            'POST',
            "/v2/courtorder/{$courtOrder->getCourtOrderUid()}/lay-deputy-invite",
            ['AuthToken' => $token, 'mustSucceed' => true, 'data' => $postData]
        );

        // assertions
        self::assertEquals(true, $responseJson['success']);
        self::assertMatchesRegularExpression('/[0-9a-z]{40}/', $responseJson['data']['registrationToken']);
    }
}
