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

        $this->mockToken = $this->createMock(TokenInterface::class);

        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

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
        $this->mockToken->expects($this->once())->method('getUser')->willReturn($this->user);
        $this->tokenStorage->expects($this->once())->method('getToken')->willReturn($this->mockToken);

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
     * All other paths through this method are captured by the tests for getLayDeputyHomepage(), except for this one,
     * which I am frankly surprised ever happens.
     */
    public function testGetCorrectRouteIfDifferentNonAdminCodepVerification()
    {
        $this->user->expects($this->once())->method('hasAdminRole')->willReturn(false);
        $this->user->expects($this->once())->method('getIsCoDeputy')->willReturn(true);
        $this->user->expects($this->once())->method('getCoDeputyClientConfirmed')->willReturn(true);

        $correctedRoute = $this->sut->getCorrectRouteIfDifferent($this->user, 'codep_verification');

        $this->assertEquals('lay_home', $correctedRoute);
    }
}
