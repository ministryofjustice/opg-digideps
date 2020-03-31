<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\Service\Redirector;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

class CoDeputyControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testAddAction(): void
    {
        $user = $this->mockLoggedInUser(['ROLE_LAY_DEPUTY']);
        $user->setCoDeputyClientConfirmed(true);
        $user->setIsCoDeputy(true);

        $emailAddress = 'invited@mailbox.example';
        $invitedUser = (new User())
            ->setId(17)
            ->setEmail($emailAddress)
            ->setRoleName(User::ROLE_LAY_DEPUTY)
            ->setRegistrationToken('invitation-token');

        $this->restClient
            ->post('codeputy/add', Argument::any(), ['codeputy'], 'User')
            ->shouldBeCalled()
            ->willReturn($invitedUser);

        $this->restClient
            ->put('user/1', ['co_deputy_client_confirmed' => true], [])
            ->shouldBeCalled();

        $this->injectProphecyService(Redirector::class, function ($redirector) {
            $redirector
                ->getCorrectRouteIfDifferent(Argument::type(User::class), Argument::type('string'))
                ->shouldBeCalled()
                ->willReturn(false);
        }, ['redirector_service']);

        $mailSender = $this->injectProphecyService(MailSender::class);
        $mailSender
            ->send(Argument::that(function ($email) use ($emailAddress) {
                return $email instanceof Email
                && $email->getToEmail() === $emailAddress
                && $email->getTemplate() === MailFactory::INVITATION_LAY_TEMPLATE_ID
                && strpos($email->getParameters()['link'], "user/activate/invitation-token") !== false;
            }))
            ->shouldBeCalled()
            ->willReturn();

        $crawler = $this->client->request('GET', '/codeputy/25/add');
        $button = $crawler->selectButton('Send invitation');

        $this->client->submit($button->form(), [
            'co_deputy_invite[email]' => $emailAddress,
        ]);

        /** @var Response $response */
        $response = $this->client->getResponse();

        self::assertEquals(302, $response->getStatusCode());

        $this->client->request('GET', '/codeputy/25/add');
        $response = $this->client->getResponse();

        self::assertStringContainsString('Deputy invitation has been sent', $response->getContent());
    }

    public function testResendActivationAction(): void
    {
        $this->mockLoggedInUser(['ROLE_LAY_DEPUTY']);

        $emailAddress = 'invited@mailbox.example';
        $invitedUser = (new User())
            ->setId(17)
            ->setEmail($emailAddress)
            ->setRoleName(User::ROLE_LAY_DEPUTY)
            ->setRegistrationToken('invitation-token');

        $this->restClient
            ->userRecreateToken($emailAddress, 'pass-reset')
            ->shouldBeCalled()
            ->willReturn($invitedUser);

        $mailSender = $this->injectProphecyService(MailSender::class);
        $mailSender
            ->send(Argument::that(function ($email) use ($emailAddress) {
                return $email instanceof Email
                && $email->getToEmail() === $emailAddress
                && $email->getTemplate() === MailFactory::INVITATION_LAY_TEMPLATE_ID
                && strpos($email->getParameters()['link'], "user/activate/invitation-token") !== false;
            }))
            ->shouldBeCalled()
            ->willReturn();

        $crawler = $this->client->request('GET', "/codeputy/re-invite/$emailAddress");
        $button = $crawler->selectButton('Resend invitation');
        $this->client->submit($button->form(), []);
        $this->client->followRedirect();
        $this->client->followRedirect();

        /** @var Response $response */
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertIsString($response->getContent());
        self::assertStringContainsString('Deputy invitation was re-sent', $response->getContent());
    }
}
