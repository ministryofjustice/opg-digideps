<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Exception;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminIndexControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockLoggedInUser(['ROLE_ADMIN']);
    }

    public function testAddUserAction(): void
    {
        $this->restClient->post('user', Argument::any(), ['admin_add_user'], 'User')->shouldBeCalled()->willReturn(new User());

        $this->injectProphecyService(MailFactory::class, function($mailFactory) {
            $mailFactory->createActivationEmail(new User())->shouldBeCalled()->willReturn(new Email());
        });

        $this->injectProphecyService(MailSender::class, function($mailSender) {
            $mailSender->send(new Email())->shouldBeCalled()->willReturn();
        });

        $crawler = $this->client->request('GET', "/admin/user-add");
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

        $this->restClient->userRecreateToken($emailAddress, 'pass-reset')->shouldBeCalled()->willReturn(new User());

        $this->injectProphecyService(MailFactory::class, function ($mailFactory) {
            $mailFactory->createActivationEmail(new User())->shouldBeCalled()->willReturn(new Email());
        });

        $this->injectProphecyService(MailSender::class, function ($mailSender) {
            $mailSender->send(new Email())->shouldBeCalled()->willReturn();
        });

        $this->injectProphecyService(LoggerInterface::class, function ($logger) {
            $logger->log(Argument::cetera())->shouldNotBeCalled();
        }, ['logger']);

        $this->client->request('GET', "/admin/send-activation-link/{$emailAddress}");

        /** @var Response $response */
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('[Link sent]', $response->getContent());
    }

    public function testSendActivationLinkSwallowsFailures(): void
    {
        $emailAddress = 'test@gmail.example';

        $this->restClient
            ->userRecreateToken($emailAddress, 'pass-reset')
            ->shouldBeCalled()
            ->willThrow(new Exception('Intentional test exception'));

        $this->injectProphecyService(LoggerInterface::class, function ($logger) {
            $logger->debug('Intentional test exception')->shouldBeCalled();
        }, ['logger']);

        $this->client->request('GET', "/admin/send-activation-link/{$emailAddress}");

        /** @var Response $response */
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('[Link sent]', $response->getContent());
    }
}
