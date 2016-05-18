<?php

namespace AppBundle\Service;

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
        $this->security = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->router = m::mock('Symfony\Component\Routing\RouterInterface');
        $this->session = m::mock('Symfony\Component\HttpFoundation\Session\Session');
        $this->restClient = m::mock('AppBundle\Service\Client\RestClient');

        $this->report = m::stub('AppBundle\Entity\Report');
        $this->client = m::mock('AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(2)
            ->shouldReceive('getReports')->andReturn([3])
            ->getMock();

        $this->user = m::stub('AppBundle\Entity\User', [
            'getClients' => [$this->client],
        ]);

        $this->security->shouldReceive('getToken->getUser')->andReturn($this->user);

        $this->object = new Redirector($this->security, $this->router, $this->session, $this->restClient, 'prod');
    }

    public function testgetFirstPageAfterLoginAdmin()
    {
        $this->security->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(true);

        $this->router
            ->shouldReceive('generate')->with('admin_homepage')->andReturn('url');

        $this->assertEquals('url', $this->object->getFirstPageAfterLogin());
    }

    public function testFirstPageLayNoDetails()
    {
        $this->security
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false)
            ->shouldReceive('isGranted')->with('ROLE_LAY_DEPUTY')->andReturn(true);

        $this->router
            ->shouldReceive('generate')->with('user_details')->andReturn('url');

        $this->user
            ->shouldReceive('hasDetails')->andReturn(false)
            ->shouldReceive('hasClients')->andReturn(false);

        $this->client
            ->shouldReceive('hasDetails')->andReturn(false);

        $this->assertEquals('url', $this->object->getFirstPageAfterLogin());
    }

    public function testFirstPageClientNoDetails()
    {
        $this->user
            ->shouldReceive('hasDetails')->andReturn(true)
            ->shouldReceive('hasClients')->andReturn(true);

        $this->client
            ->shouldReceive('hasDetails')->andReturn(false);

        $this->security
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false)
            ->shouldReceive('isGranted')->with('ROLE_LAY_DEPUTY')->andReturn(true);

        $this->router
            ->shouldReceive('generate')->with('client_add')->andReturn('url');

        $this->assertEquals('url', $this->object->getFirstPageAfterLogin());
    }

    public function testFirstPageLayDetailsNoClient()
    {
        $this->security
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false)
            ->shouldReceive('isGranted')->with('ROLE_LAY_DEPUTY')->andReturn(true);

        $this->router
            ->shouldReceive('generate')->with('client_add')->andReturn('url');

        $this->user
            ->shouldReceive('hasDetails')->andReturn(true)
            ->shouldReceive('hasClients')->andReturn(false);

        $this->assertEquals('url', $this->object->getFirstPageAfterLogin());
    }

    public function testFirstPageLayDetailsClientNoReports()
    {
        $this->security
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false)
            ->shouldReceive('isGranted')->with('ROLE_LAY_DEPUTY')->andReturn(true);

        $this->router
            ->shouldReceive('generate')->with('report_create', ['clientId' => 2])->andReturn('url');

        $this->user
            ->shouldReceive('hasDetails')->andReturn(true)
            ->shouldReceive('hasClients')->andReturn(true)
            ->shouldReceive('hasReports')->andReturn(false);

        $this->client
            ->shouldReceive('hasDetails')->andReturn(true);

        $this->assertEquals('url', $this->object->getFirstPageAfterLogin());
    }

    public function testFirstPageLayDetailsClientReportsLur()
    {
        $this->security
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false)
            ->shouldReceive('isGranted')->with('ROLE_LAY_DEPUTY')->andReturn(true);

        $this->session->shouldReceive('get')->with('_security.secured_area.target_path')->andReturn('http://example.org/path');
        $this->router->shouldReceive('match')->with('/path')->andReturn(['_route' => 'user_details']);

        $this->user
            ->shouldReceive('hasDetails')->andReturn(true)
            ->shouldReceive('hasClients')->andReturn(true)
            ->shouldReceive('hasReports')->andReturn(true);

        $this->client
            ->shouldReceive('hasDetails')->andReturn(true);

        $this->assertEquals('http://example.org/path', $this->object->getFirstPageAfterLogin());
    }

    public function testFirstPageLayDetailsClientReports()
    {
        $this->security
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false)
            ->shouldReceive('isGranted')->with('ROLE_LAY_DEPUTY')->andReturn(true);

        $this->router
            ->shouldReceive('generate')->with('report_overview', ['reportId' => 3])->andReturn('url');

        $this->report
            ->shouldReceive('getSubmitted')->andReturn(false);

        $this->restClient
            ->shouldReceive('get')->with('report/3', 'Report', m::any())->andReturn($this->report);

        $this->user
            ->shouldReceive('hasDetails')->andReturn(true)
            ->shouldReceive('hasClients')->andReturn(true)
            ->shouldReceive('hasReports')->andReturn(true);

        $this->client
            ->shouldReceive('hasDetails')->andReturn(true);

        $this->assertEquals('url', $this->object->getFirstPageAfterLogin(false));
    }

    public function testFirstPageLayDetailsClientReportsFallBackHome()
    {
        $this->security
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false)
            ->shouldReceive('isGranted')->with('ROLE_LAY_DEPUTY')->andReturn(true);

        $this->router
            ->shouldReceive('generate')->with('client')->andReturn('url');

        $this->report
            ->shouldReceive('getSubmitted')->andReturn(true);

        $this->restClient
            ->shouldReceive('get')->with('report/3', 'Report', m::any())->andReturn($this->report);

        $this->user
            ->shouldReceive('hasDetails')->andReturn(true)
            ->shouldReceive('hasClients')->andReturn(true)
            ->shouldReceive('hasReports')->andReturn(true);

        $this->client
            ->shouldReceive('hasDetails')->andReturn(true);

        $this->assertEquals('url', $this->object->getFirstPageAfterLogin(false));
    }

    public function testGetHomepageRedirectDeputyRedirectNotLogged()
    {
        $this->security
            ->shouldReceive('isGranted')->with('IS_AUTHENTICATED_FULLY')->andReturn(false);

        $this->assertEquals(false, $this->object->getHomepageRedirect());
    }

    public function testGetHomepageRedirectDeputyOverviewPage()
    {
        $this->security
            ->shouldReceive('isGranted')->with('IS_AUTHENTICATED_FULLY')->andReturn(true)
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false)
            ->shouldReceive('isGranted')->with('ROLE_LAY_DEPUTY')->andReturn(true);

        $this->router
            ->shouldReceive('generate')->with('report_overview', ['reportId' => 3])->andReturn('url');

        $this->report
            ->shouldReceive('getSubmitted')->andReturn(false);

        $this->restClient
            ->shouldReceive('get')->with('report/3', 'Report', m::any())->andReturn($this->report);

        $this->user
            ->shouldReceive('hasDetails')->andReturn(true)
            ->shouldReceive('hasClients')->andReturn(true)
            ->shouldReceive('hasReports')->andReturn(true);

        $this->client
            ->shouldReceive('hasDetails')->andReturn(true);

        $this->assertEquals('url', $this->object->getHomepageRedirect());
    }

    public function testGetHomepageRedirectAdminLogged()
    {
        $this->security
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(true);

        $this->router
            ->shouldReceive('generate')->with('admin_homepage')->andReturn('url');

        $redirectorAdmin = new Redirector($this->security, $this->router, $this->session, $this->restClient, 'admin');

        $this->assertEquals('url', $redirectorAdmin->getHomepageRedirect());
    }

    public function testGetHomepageRedirectAdLogged()
    {
        $this->security
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false)
            ->shouldReceive('isGranted')->with('ROLE_AD')->andReturn(true);

        $this->router
            ->shouldReceive('generate')->with('ad_homepage')->andReturn('url');

        $redirectorAdmin = new Redirector($this->security, $this->router, $this->session, $this->restClient, 'admin');

        $this->assertEquals('url', $redirectorAdmin->getHomepageRedirect());
    }

    public function testGetHomepageRedirectAdminNotLogged()
    {
        $this->security
            ->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturn(false)
            ->shouldReceive('isGranted')->with('ROLE_AD')->andReturn(false);

        $this->router
            ->shouldReceive('generate')->with('login')->andReturn('url');

        $redirectorAdmin = new Redirector($this->security, $this->router, $this->session, $this->restClient, 'admin');

        $this->assertEquals('url', $redirectorAdmin->getHomepageRedirect());
    }

    public function tearDown()
    {
        m::close();
    }
}
