<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Admin\IndexController;
use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Exception;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

class AdminIndexControllerTest extends AbstractControllerTestCase
{
    /** @var IndexController */
    protected $sut;

    public function setUp(): void
    {
        parent::setUp();

        $container = $this->client->getContainer();

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn(new User());
        $token->serialize()->willReturn('');
        $token->isAuthenticated()->willReturn(true);
        $token->getRoles()->willReturn([new Role('ROLE_ADMIN')]);

        $tokenStorage = self::prophesize(TokenStorage::class);
        $tokenStorage->getToken()->willReturn($token);
        $tokenStorage->setToken(null)->willReturn();

        $container->set('security.token_storage', $tokenStorage->reveal());
    }

    public function testSendActivationLink(): void
    {
        $emailAddress = 'test@gmail.example';
        $container = $this->client->getContainer();

        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSender::class);
        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $restClient->userRecreateToken($emailAddress, 'pass-reset')->shouldBeCalled()->willReturn(new User());
        $mailFactory->createActivationEmail(new User())->shouldBeCalled()->willReturn(new Email());
        $mailSender->send(new Email(), Argument::cetera())->shouldBeCalled()->willReturn();
        $logger->log(Argument::cetera())->shouldNotBeCalled();

        $container->set(MailFactory::class, $mailFactory->reveal());
        $container->set(MailSender::class, $mailSender->reveal());
        $container->set('logger', $logger->reveal());
        $container->set(RestClient::class, $restClient->reveal());

        $this->client->request('GET', "/admin/send-activation-link/{$emailAddress}");
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertStringContainsString('[Link sent]', $response->getContent());
    }

    public function testSendActivationLinkSwallowsFailures(): void
    {
        $emailAddress = 'test@gmail.example';
        $container = $this->client->getContainer();

        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSender::class);
        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $restClient
            ->userRecreateToken($emailAddress, 'pass-reset')
            ->shouldBeCalled()
            ->willThrow(new Exception('Intentional test exception'));

        $logger->debug('Intentional test exception')->shouldBeCalled();

        $container->set(MailFactory::class, $mailFactory->reveal());
        $container->set(MailSender::class, $mailSender->reveal());
        $container->set('logger', $logger->reveal());
        $container->set(RestClient::class, $restClient->reveal());

        $this->client->request('GET', "/admin/send-activation-link/{$emailAddress}");
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertStringContainsString('[Link sent]', $response->getContent());
    }
}
