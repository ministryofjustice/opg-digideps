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
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AdminIndexControllerTest extends WebTestCase
{
    /** @var IndexController */
    private $sut;

    /** @var RouterInterface */
    private static $router;

    public static function setUpBeforeClass(): void
    {
        $client = self::createClient();
        self::$router = $client->getContainer()->get('router');
    }

    public function setUp(): void
    {
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

    // Mock container and all of the dependent services
    public function testAddUserWithHeavyMocking(): void
    {
        $orgService = self::prophesize(OrgService::class);
        $sut = new IndexController($orgService->reveal());

        $form = self::prophesize(Form::class);
        $form->handleRequest(Argument::type(Request::class))->willReturn();
        $form->createView()->willReturn('form-view');

        $formFactory = self::prophesize(FormFactory::class);
        $formFactory->create(Argument::cetera())->willReturn($form->reveal());

        $container = new Container();
        $container->set('form.factory', $formFactory->reveal());
        $sut->setContainer($container);

        $form->isValid()->willReturn(false);

        // --------

        $request = self::prophesize(Request::class);
        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSenderInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $response = $sut->addUserAction($request->reveal(), $restClient->reveal(), $mailFactory->reveal(), $mailSender->reveal());

        self::assertArrayHasKey('form', $response);
    }

    // Partial-mock IndexController and interrupt Controller functions
    public function testAddUserWithMockery(): void
    {
        $form = self::prophesize(Form::class);
        $form->handleRequest(Argument::type(Request::class))->willReturn();
        $form->isValid()->willReturn(false);
        $form->createView()->willReturn('form-view');

        $sut = \Mockery::mock(IndexController::class);
        $sut->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('createForm')->andReturn($form->reveal());
        $sut->makePartial();

        // --------

        $request = self::prophesize(Request::class);
        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSenderInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $response = $sut->addUserAction($request->reveal(), $restClient->reveal(), $mailFactory->reveal(), $mailSender->reveal());

        self::assertArrayHasKey('form', $response);
    }

    // Use a real container
    public function testAddUserWithKernel(): void
    {
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();

        $user = new User();
        $user->setRoleName('ROLE_ADMIN');

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);
        $tokenStorage = self::prophesize(TokenStorage::class);
        $tokenStorage->getToken()->willReturn($token);
        $container->set('security.token_storage', $tokenStorage->reveal());

        $sut = new IndexController(self::prophesize(OrgService::class)->reveal());
        $sut->setContainer($container);

        // --------

        $request = self::prophesize(Request::class);
        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSenderInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $response = $sut->addUserAction($request->reveal(), $restClient->reveal(), $mailFactory->reveal(), $mailSender->reveal());

        self::assertArrayHasKey('form', $response);
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
