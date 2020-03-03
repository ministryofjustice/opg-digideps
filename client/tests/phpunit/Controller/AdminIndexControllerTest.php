<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Admin\IndexController;
use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSenderInterface;
use AppBundle\Service\OrgService;
use Exception;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

class AdminIndexControllerTest extends WebTestCase
{
    /** @var IndexController */
    private $sut;

    /** @var Container */
    private $container;

    /** @var RouterInterface */
    private static $router;

    public static function setUpBeforeClass(): void
    {
        $client = self::createClient(['environment' => 'unittest']);
        self::$router = $client->getContainer()->get('router');
    }

    public function setUp(): void
    {
        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn(new User());
        $token->isAuthenticated()->willReturn(true);
        $token->getRoles()->willReturn([new Role('ROLE_ADMIN')]);
        $tokenStorage = self::prophesize(TokenStorage::class);
        $tokenStorage->getToken()->willReturn($token);

        $this->container = self::$kernel->getContainer();
        $this->container->set('security.token_storage', $tokenStorage->reveal());

        $this->sut = new IndexController(self::prophesize(OrgService::class)->reveal());
    }

    public function getRouteMap()
    {
        return [
            ['/admin/', 'indexAction'],
            ['/admin/user-add', 'addUserAction'],
            ['/admin/edit-user', 'editUserAction'],
            ['/admin/send-activation-link/test@email.com', 'sendUserActivationLinkAction', ['email' => 'test@email.com']],
        ];
    }

    /**
     * @dataProvider getRouteMap
     */
    public function testRoutes(string $url, string $action, array $params = []): void
    {
        $match = self::$router->match($url);

        self::assertEquals(get_class($this->sut) . '::' . $action, $match['_controller']);
        foreach ($params as $key => $expectedValue) {
            self::assertEquals($expectedValue, $match[$key]);
        }
    }

    // Use a real container
    public function testAddUserSubmit(): void
    {
        $this->sut->setContainer($this->container);

        $request = new Request([], [
            'admin' => [
                'email' => 'teset@test.example',
                'firstname' => 'Test',
                'lastname' => 'User',
                'roleName' => 'ROLE_ADMIN',
            ],
        ]);

        $request->setMethod('POST');

        $mailFactory = self::prophesize(MailFactory::class);
        $mailFactory->createActivationEmail(Argument::type(User::class))->willReturn(new Email());

        $mailSender = self::prophesize(MailSenderInterface::class);
        $mailSender->send(Argument::type(Email::class), ['text', 'html'])->willReturn(true);

        $restClient = self::prophesize(RestClient::class);
        $restClient->post('user', Argument::type(User::class), ['admin_add_user'], 'User')->willReturnArgument(1);

        $response = $this->sut->addUserAction($request, $restClient->reveal(), $mailFactory->reveal(), $mailSender->reveal());

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertEquals('/admin/', $response->getTargetUrl());
    }

    public function testSendActivationLink(): void
    {
        $emailAddress = 'test@gmail.example';

        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSenderInterface::class);
        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $restClient->userRecreateToken($emailAddress, 'pass-reset')->shouldBeCalled()->willReturn(new User());
        $mailFactory->createActivationEmail(new User())->shouldBeCalled()->willReturn(new Email());
        $mailSender->send(new Email(), Argument::cetera())->shouldBeCalled()->willReturn();
        $logger->log(Argument::cetera())->shouldNotBeCalled();

        $response = $this->sut->sendUserActivationLinkAction($emailAddress, $mailFactory->reveal(), $mailSender->reveal(), $logger->reveal(), $restClient->reveal());

        self::assertEquals(200, $response->getStatusCode());
        self::assertStringContainsString('[Link sent]', $response->getContent());
    }

    public function testSendActivationLinkSwallowsFailures(): void
    {
        $emailAddress = 'test@gmail.example';

        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSenderInterface::class);
        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $restClient
            ->userRecreateToken($emailAddress, 'pass-reset')
            ->shouldBeCalled()
            ->willThrow(new Exception('Intentional test exception'));

        $logger->debug('Intentional test exception')->shouldBeCalled();

        $response = $this->sut->sendUserActivationLinkAction($emailAddress, $mailFactory->reveal(), $mailSender->reveal(), $logger->reveal(), $restClient->reveal());

        self::assertEquals(200, $response->getStatusCode());
        self::assertStringContainsString('[Link sent]', $response->getContent());
    }
}
