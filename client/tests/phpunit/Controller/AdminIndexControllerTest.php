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

class AdminIndexControllerTest extends AbstractControllerTestCase
{
    /** @var IndexController */
    protected $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockLoggedInUser(['ROLE_ADMIN']);
    }

    public function testAddUserAction(): void
    {
        $container = $this->client->getContainer();

        $restClient = self::prophesize(RestClient::class);
        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSender::class);

        $restClient->setLoggedUserId(1)->willReturn($restClient->reveal());
        $restClient->get('user/1', Argument::cetera())->shouldBeCalled()->willReturn(new User());

        $restClient->post('user', Argument::any(), ['admin_add_user'], 'User')->shouldBeCalled()->willReturn(new User());
        $mailFactory->createActivationEmail(new User())->shouldBeCalled()->willReturn(new Email());
        $mailSender->send(new Email(), Argument::cetera())->shouldBeCalled()->willReturn();
        $container->set(RestClient::class, $restClient->reveal());
        $container->set('rest_client', $restClient->reveal());
        $container->set(MailFactory::class, $mailFactory->reveal());
        $container->set(MailSender::class, $mailSender->reveal());

        $crawler = $this->client->request('GET', "/admin/user-add");
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());

        $button = $crawler->selectButton('Save user');

        $this->client->submit($button->form(), [
            'admin[email]' => 'test@mailbox.example',
            'admin[firstname]' => 'Ross',
            'admin[lastname]' => 'Niewieroski',
            'admin[roleType]' => 'staff',
            'admin[roleNameStaff]' => 'ROLE_ADMIN',
        ]);
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
