<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Entity\User;
use MockeryStub as m;

class RedirectorTest extends \PHPUnit_Framework_TestCase
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
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @var Session
     */
    protected $session;

    public function setUp()
    {
        $this->user = m::mock(User::class);
        $this->security = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->security->shouldReceive('getToken->getUser')->andReturn($this->user);
        $this->router = m::mock('Symfony\Component\Routing\RouterInterface')
            ->shouldReceive('generate')->andReturnUsing(function($route, $params = []) {
            return [$route, $params];
        })->getMock();
        $this->session = m::mock('Symfony\Component\HttpFoundation\Session\Session');

        $this->security->shouldReceive('getToken->getUser')->andReturn($this->user);

        $this->object = new Redirector($this->security, $this->router, $this->session, 'prod');
    }

    public static function firstPageAfterLoginProvider()
    {
        $clientWithDetails = m::mock(Client::class, ['hasDetails'=>true]);
        $clientWithoutDetails = m::mock(Client::class)->shouldReceive('hasDetails')->andReturn(false)->getMock();

        return [
           ['ROLE_ADMIN', [], ['admin_homepage', []]],
           ['ROLE_LAY_DEPUTY', ['hasDetails'=>false], ['user_details', []]],
           ['ROLE_LAY_DEPUTY', ['hasDetails'=>true, 'getIdOfClientWithDetails'=>null], ['client_add', []]],
           ['ROLE_LAY_DEPUTY', ['hasDetails'=>true, 'getIdOfClientWithDetails'=>1, 'getActiveReportId'=>1], ['report_overview', ['reportId'=>1]]],
           ['ROLE_LAY_DEPUTY', ['hasDetails'=>true, 'getIdOfClientWithDetails'=>1, 'getActiveReportId'=>null], ['odr_index', []]],
        ];
    }

    /**
     * @dataProvider firstPageAfterLoginProvider
     */
    public function testgetFirstPageAfterLogin($grantedRole, $userMocks, $expectedRouteAndParams)
    {
        $this->markTestIncomplete('fix when specs are 100% defined');
        
        $this->security->shouldIgnoreMissing();
        $this->security->shouldReceive('isGranted')->with($grantedRole)->andReturn(true);
        foreach($userMocks as $k => $v) {
            $this->user->shouldReceive($k)->andReturn($v);
        }

        $actual = $this->object->getFirstPageAfterLogin(false);
        $this->assertEquals($actual, $expectedRouteAndParams);
    }

    public function tearDown()
    {
        m::close();
    }
}
