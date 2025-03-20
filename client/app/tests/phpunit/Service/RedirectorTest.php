<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Service\Client\Internal\ClientApi;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RedirectorTest extends TestCase
{
    private User $user;
    private TokenStorageInterface $tokenStorage;
    private Session $session;
    private RouterInterface $router;
    private AuthorizationCheckerInterface $authChecker;
    private ClientApi $clientApi;
    private LoggerInterface $logger;

    private Redirector $sut;

    public function setUp(): void
    {
        $this->user = $this->createMock(User::class);

        $this->router = $this->createMock(RouterInterface::class);

        $this->session = $this->createMock(Session::class);

        $mockToken = $this->createMock(TokenInterface::class);
        $mockToken->expects($this->once())->method('getUser')->willReturn($this->user);

        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->tokenStorage->expects($this->once())->method('getToken')->willReturn($mockToken);

        $this->authChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->clientApi = $this->createMock(ClientApi::class);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sut = new Redirector($this->tokenStorage, $this->authChecker, $this->router, $this->session, 'prod', $this->clientApi, $this->logger);
    }

    public static function firstPageAfterLoginProvider(): array
    {
        // $grantedRole: the role the user has when checked via the auth checker; null if this is irrelevant
        // $paOrProfDeputy: true if the user is a PA or PROF deputy
        // $coDeputy: true if user is an invited co-deputy
        // $coDeputyClientConfirmed: true if user is an invited co-deputy who has confirmed the client
        // $clientIdWithDetails: values returned for User::getClientIdWithDetails; null if hasn't been set for user
        // $userHasAddress: true if user has address details
        // $sessionValue: key => value pairs to set in the session; if not set, any call to Session::has will return false
        // $clients: number of clients to return for lay deputy; 0 for non-lay deputies, as it's not used for redirects
        // $reports: number of reports to return for each client (if there are any clients)
        // $userIsNdrEnabled: true if the user needs to complete an NDR
        // $routeName: the route name we expect will be passed to the router
        // $routeParams: array of params we expect to be passed to the router
        // $expectedRoute: the route we expect to see coming out of the router (what we're asserting on)
        return [
            'admin password create' => [User::ROLE_ADMIN, false, false, false, null, true, ['login-context' => 'password-create'], 0, 0, false, 'user_details', [], '/user/details'],
            'admin homepage' => [User::ROLE_ADMIN, false, false, false, null, true, null, 0, 0, false, 'admin_homepage', [], '/admin/'],
            'ad homepage' => [User::ROLE_AD, false, false, false, null, true, null, 0, 0, false, 'ad_homepage', [], '/ad/'],
            'non-admin password create' => [null, true, false, false, null, true, ['login-context' => 'password-create'], 0, 0, false, 'user_details', [], '/user/details'],
            'non-admin org dashboard' => [null, true, false, false, null, true, null, 0, 0, false, 'org_dashboard', [], '/org/'],
            'lay with multiple clients' => [User::ROLE_LAY_DEPUTY, false, false, false, null, true, null, 2, 1, false, 'choose_a_client', [], '/choose-a-client'],
            'lay with single client' => [User::ROLE_LAY_DEPUTY, false, false, false, null, true, null, 1, 1, false, 'lay_home', ['clientId' => 999], '/client/999'],
            'co-deputy lay with single client, not confirmed' => [User::ROLE_LAY_DEPUTY, false, true, false, null, true, null, 1, 0, false, 'codep_verification', [], '/codeputy/verification'],
            'co-deputy lay with single client, confirmed, client has reports' => [User::ROLE_LAY_DEPUTY, false, true, true, null, true, null, 1, 1, false, 'lay_home', ['clientId' => 999], '/client/999'],
            'co-deputy lay with single client, confirmed, client has no reports, ndr enabled' => [User::ROLE_LAY_DEPUTY, false, true, true, null, true, null, 1, 0, true, 'lay_home', ['clientId' => 999], '/client/999'],
            'co-deputy lay with single client, confirmed, client has no reports, not ndr enabled' => [User::ROLE_LAY_DEPUTY, false, true, true, 1111, true, null, 1, 0, false, 'report_create', ['clientId' => 1111], '/report/create/1111'],
            'lay with no clients added and address details' => [User::ROLE_LAY_DEPUTY, false, false, false, null, true, null, 0, 0, false, 'client_add', [], '/client/add'],
            'lay with no clients added and no address details' => [User::ROLE_LAY_DEPUTY, false, false, false, null, false, null, 0, 0, false, 'user_details', [], '/user/details'],
            'access denied' => [null, false, false, false, null, false, null, 0, 0, false, 'access_denied', [], '/access-denied'],
        ];
    }

    /**
     * @dataProvider firstPageAfterLoginProvider
     */
    public function testGetFirstPageAfterLogin(
        ?string $grantedRole,
        bool $paOrProfDeputy,
        bool $coDeputy,
        bool $coDeputyClientConfirmed,
        ?int $clientIdWithDetails,
        bool $userHasAddress,
        ?array $sessionValues,
        int $numClients,
        int $numReports,
        bool $userIsNdrEnabled,
        string $routeName,
        array $routeParams,
        string $expectedRoute,
    ): void {
        $this->authChecker->expects($this->any())
            ->method('isGranted')
            ->willReturnCallback(function ($role) use ($grantedRole) {
                return $role === $grantedRole;
            });

        $this->user->method('isDeputyOrg')->willReturn($paOrProfDeputy);
        $this->user->method('hasAdminRole')->willReturn(User::ROLE_LAY_DEPUTY !== $grantedRole);
        $this->user->method('getIsCoDeputy')->willReturn($coDeputy);
        $this->user->method('hasAddressDetails')->willReturn($userHasAddress);
        $this->user->method('isNdrEnabled')->willReturn($userIsNdrEnabled);
        $this->user->method('getIdOfClientWithDetails')->willReturn($clientIdWithDetails);

        if ($coDeputy) {
            $this->user->method('getRegistrationRoute')->willReturn(User::CO_DEPUTY_INVITE);
            $this->user->method('getCoDeputyClientConfirmed')->willReturn($coDeputyClientConfirmed);
        }

        if (is_null($sessionValues)) {
            $this->session->method('get')->willReturn(null);
        } else {
            foreach ($sessionValues as $key => $value) {
                $this->session->expects($this->once())->method('get')->with($key)->willReturn($value);
            }
        }

        if ($numClients > 0) {
            $clients = [];
            for ($i = 0; $i < $numClients; ++$i) {
                $client = $this->createMock(Client::class);
                $client->method('getId')->willReturn(999);

                $reports = [];
                for ($j = 0; $j < $numReports; ++$j) {
                    $report = $this->createMock(Report::class);
                    $reports[] = $report;
                }
                $client->method('getReportIds')->willReturn($reports);

                $clients[] = $client;
            }

            $this->user->method('getDeputyUid')->willReturn(777777);
            $this->clientApi->method('getAllClientsByDeputyUid')
                ->willReturn($clients);
        }

        if (!is_null($routeName)) {
            $this->router->expects($this->once())
                ->method('generate')
                ->with($routeName, $routeParams)
                ->willReturn($expectedRoute);
        }

        $actual = $this->sut->getFirstPageAfterLogin($this->session);

        $this->assertEquals($actual, $expectedRoute);
    }

    /*
    public static function getCorrectRouteIfDifferentProvider()
    {
        // ROLE, current_route, isCoDeputy, coDeputyClientConfirmed, isNdrEnabled, isDeputyOrg,  clientKnown, hasAddress
        return [
            // Same URLs never get redirected
            ['ROLE_LAY_DEPUTY', 'lay_home',  false, false,  false,  false, true, true, false],

            // NDR deputy gets redirected to NDR index
            ['ROLE_LAY_DEPUTY', 'lay_home',  false, false,  true,  false, true, true, false],
            // Lay deputy gets redirected to LAY

            // Correct URLs dont get redirected
            ['ROLE_LAY_DEPUTY', 'lay_home',  false, false,  false,  false, true, true, false],

            // User without client gets redirected to client_add
            ['ROLE_LAY_DEPUTY', 'lay_home', false, false,  true, false, false, true, 'client_add'],

            // User without user address gets redirected to user_details
            ['ROLE_LAY_DEPUTY', 'lay_home', false, false,  true, false, true, false, 'user_details'],

            // Unverified co deputies gets redirected to codep_verification
            ['ROLE_LAY_DEPUTY', 'lay_home', true, false, false, false, true, false, 'codep_verification'],

            // Verified co deputies dont get redirected
            ['ROLE_LAY_DEPUTY', 'lay_home', true, true, false, false, true, false, false],

            // Admins are not redirected
            [User::ROLE_ADMIN, 'lay_home', true, true, false, false, true, false, false],

            // Profs/PAs dont get redirected as we assume that we have client and address details
            [User::ROLE_PA_NAMED, 'lay_home', true, true, false, false, true, false, false],
            [User::ROLE_PA_ADMIN, 'lay_home', true, true, false, false, true, false, false],
            [User::ROLE_PA_TEAM_MEMBER, 'lay_home', true, true, false, false, true, false, false],
            [User::ROLE_PROF_NAMED, 'lay_home', true, true, false, false, true, false, false],
            [User::ROLE_PROF_ADMIN, 'lay_home', true, true, false, false, true, false, false],
            [User::ROLE_PROF_TEAM_MEMBER, 'lay_home', true, true, false, false, true, false, false],
        ];
    }*/

    /*
     * @dataProvider getCorrectRouteIfDifferentProvider
     */
    /*
    public function testGetCorrectRouteIfDifferent(
        $userRole,
        $currentRoute,
        $isCoDeputy,
        $coDeputyClientConfirmed,
        $isNdrEnabled,
        $isDeputyOrg,
        $clientKnown,
        $hasAddress,
        $expectedRoute,
        $registrationRoute = User::CO_DEPUTY_INVITE,
    ) {
        $this->user->setRoleName($userRole);

        $this->user->shouldReceive('isNdrEnabled')->andReturn($isNdrEnabled);
        $this->user->shouldReceive('getIsCoDeputy')->andReturn($isCoDeputy);
        $this->user->shouldReceive('getCoDeputyClientConfirmed')->andReturn($coDeputyClientConfirmed);
        $this->user->shouldReceive('isDeputyOrg')->andReturn($isDeputyOrg);
        $this->user->shouldReceive('getIdOfClientWithDetails')->andReturn($clientKnown);
        $this->user->shouldReceive('hasAddressDetails')->andReturn($hasAddress);
        $this->user->shouldReceive('getRegistrationRoute')->andReturn($registrationRoute);

        $correctRoute = $this->sut->getCorrectRouteIfDifferent($this->user, $currentRoute);
        $this->assertEquals($expectedRoute, $correctRoute);
    }

    public function tearDown(): void
    {
        m::close();
    }*/
}
