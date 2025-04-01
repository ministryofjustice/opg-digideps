<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\User;
use App\Service\Client\Internal\ClientApi;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RedirectorTest extends TestCase
{
    /**
     * @var Redirector
     */
    protected $object;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ClientApi
     */
    protected $clientApi;

    private User $user;
    private LoggerInterface $logger;

    /**
     * @var ParameterStoreService
     */
    protected $parameterStoreService;

    public function setUp(): void
    {
        $this->user = m::mock(User::class)->makePartial();
        $this->tokenStorage = m::mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $this->tokenStorage->shouldReceive('getToken->getUser')->andReturn($this->user);
        $this->router = m::mock('Symfony\Component\Routing\RouterInterface')
            ->shouldReceive('generate')->andReturnUsing(function ($route, $params = []) {
                return [$route, $params];
            })->getMock();

        $session = m::mock('Symfony\Component\HttpFoundation\Session\Session');
        $mockRequestStack = $this->createMock(RequestStack::class);
        $mockRequestStack->method('getSession')->willReturn($session);

        $this->tokenStorage->shouldReceive('getToken->getUser')->andReturn($this->user);

        $this->authChecker = m::mock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $this->clientApi = m::mock(ClientApi::class);
        $this->logger = m::mock(LoggerInterface::class);

        $this->object = new Redirector(
            $this->tokenStorage,
            $this->authChecker,
            $this->router,
            $mockRequestStack,
            'prod',
            $this->clientApi,
            $this->logger,
        );
    }

    public static function firstPageAfterLoginProvider()
    {
        $clientWithDetails = m::mock(Client::class, ['hasDetails' => true]);
        $clientWithoutDetails = m::mock(Client::class)->shouldReceive('hasDetails')->andReturn(false)->getMock();

        return [
            ['ROLE_ADMIN', [], ['admin_homepage', []]],
            ['ROLE_LAY_DEPUTY', ['hasDetails' => false], ['user_details', []]],
            ['ROLE_LAY_DEPUTY', ['hasDetails' => true, 'getIdOfClientWithDetails' => null], ['client_add', []]],
            ['ROLE_LAY_DEPUTY', ['hasDetails' => true, 'getIdOfClientWithDetails' => 1, 'getActiveReportId' => 1], ['report_overview', ['reportId' => 1]]],
            ['ROLE_LAY_DEPUTY', ['hasDetails' => true, 'getIdOfClientWithDetails' => 1, 'getActiveReportId' => null], ['lay_home', []]],
        ];
    }

    /**
     * @dataProvider firstPageAfterLoginProvider
     */
    public function testgetFirstPageAfterLogin($grantedRole, $userMocks, $expectedRouteAndParams)
    {
        $this->markTestIncomplete('fix when specs are 100% defined');

        $this->authChecker->shouldIgnoreMissing();
        $this->authChecker->shouldReceive('isGranted')->with($grantedRole)->andReturn(true);
        foreach ($userMocks as $k => $v) {
            $this->user->shouldReceive($k)->andReturn($v);
        }

        $actual = $this->object->getFirstPageAfterLogin(false);
        $this->assertEquals($actual, $expectedRouteAndParams);
    }

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
    }

    /**
     * @dataProvider getCorrectRouteIfDifferentProvider
     */
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

        $correctRoute = $this->object->getCorrectRouteIfDifferent($this->user, $currentRoute);
        $this->assertEquals($expectedRoute, $correctRoute);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
