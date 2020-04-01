<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Model\SelfRegisterData;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testActivateLinkSendAction(): void
    {
        $emailAddress = 'test@mailbox.example';
        $token = 'token';
        $user = new User();
        $user->setEmail($emailAddress);
        $user->setRegistrationToken('token');

        $this->restClient->loadUserByToken($token)->shouldBeCalled()->willReturn($user);
        $this->restClient->userRecreateToken($emailAddress, 'activate')->shouldBeCalled()->willReturn($user);

        $mailSender = $this->injectProphecyService(MailSender::class);
        $mailSender
            ->send(Argument::that(function ($email) use ($emailAddress, $token) {
                return $email instanceof Email
                    && $email->getToEmail() === $emailAddress
                    && $email->getTemplate() === MailFactory::ACTIVATION_TEMPLATE_ID
                    && strpos($email->getParameters()['activationLink'], "user/activate/$token") !== false;
            }))
            ->shouldBeCalled()
            ->willReturn();

        $this->client->request('GET', "/user/activate/password/send/$token");
        $this->client->followRedirect();

        /** @var Response $response */
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertIsString($response->getContent());
        self::assertStringContainsString('Check your email inbox, we&#039;ve sent you an email with a new link.', $response->getContent());
    }

    public function testRegisterAction(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $button = $crawler->selectButton('Sign up');

        $data = new SelfRegisterData();
        $data->setFirstname('Denis');
        $data->setLastname('Brauchla');
        $data->setPostcode('DB1 9FI');
        $data->setEmail('d.brauchla@mailbox.example');
        $data->setClientFirstname('Abraham');
        $data->setClientLastname('Ruhter');
        $data->setCaseNumber('13859388');

        $user = (new User())
            ->setEmail('d.brauchla@mailbox.example')
            ->setRegistrationToken('selfregister-token');

        $this->restClient->registerUser($data)->willReturn($user);

        $mailSender = $this->injectProphecyService(MailSender::class);
        $mailSender
            ->send(Argument::that(function ($email) {
                return $email instanceof Email
                    && $email->getToEmail() === 'd.brauchla@mailbox.example'
                    && $email->getTemplate() === MailFactory::ACTIVATION_TEMPLATE_ID
                    && strpos($email->getParameters()['activationLink'], 'user/activate/selfregister-token') !== false;
            }))
            ->shouldBeCalled()
            ->willReturn();

        $this->client->submit($button->form(), [
            'self_registration[firstname]' => $data->getFirstname(),
            'self_registration[lastname]' => $data->getLastname(),
            'self_registration[postcode]' => $data->getPostcode(),
            'self_registration[email][first]' => $data->getEmail(),
            'self_registration[email][second]' => $data->getEmail(),
            'self_registration[clientFirstname]' => $data->getClientFirstname(),
            'self_registration[clientLastname]' => $data->getClientLastname(),
            'self_registration[caseNumber]' => $data->getCaseNumber(),
        ]);

        /** @var Response $response */
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertIsString($response->getContent());
        self::assertStringContainsString('We\'ve sent you a link to <strong class="bold-small">d.brauchla@mailbox.example</strong>', $response->getContent());
    }

    public function testPasswordForgottenAction(): void
    {
        $emailAddress = 'test@mailbox.example';
        $user = new User();
        $user->setEmail($emailAddress);
        $user->setRegistrationToken('test');

        $this->restClient->userRecreateToken($emailAddress, 'pass-reset')->shouldBeCalled()->willReturn($user);

        $mailSender = $this->injectProphecyService(MailSender::class);
        $mailSender
            ->send(Argument::that(function ($email) use ($emailAddress) {
                return $email instanceof Email
                    && $email->getToEmail() === $emailAddress
                    && $email->getTemplate() === MailFactory::RESET_PASSWORD_TEMPLATE_ID
                    && strpos($email->getParameters()['resetLink'], 'user/password-reset/test') !== false;
            }))
            ->shouldBeCalled()
            ->willReturn();

        $crawler = $this->client->request('GET', "/password-managing/forgotten");

        $button = $crawler->selectButton('Reset your password');

        $this->client->submit($button->form(), [
            'password_forgotten[email]' => $emailAddress,
        ]);

        $this->client->followRedirect();

        /** @var Response $response */
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertIsString($response->getContent());
        self::assertStringContainsString('We have sent a new registration link to your email', $response->getContent());
    }
}
