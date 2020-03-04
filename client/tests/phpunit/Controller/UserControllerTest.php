<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Service\Mailer\MailSender;
use Prophecy\Argument;

class UserControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testPasswordForgottenAction(): void
    {
        $emailAddress = 'test@mailbox.example';
        $user = new User();
        $user->setEmail($emailAddress);
        $user->setRegistrationToken('test');

        $this->restClient->userRecreateToken($emailAddress, 'pass-reset')->shouldBeCalled()->willReturn($user);

        $this->injectProphecyService(MailSender::class, function($mailSender) use ($emailAddress) {
            $mailSender->send(Argument::that(function ($email) use ($emailAddress) {
                return $email instanceof Email
                    && $email->getToEmail() === $emailAddress
                    && strpos($email->getParameters()['resetLink'], 'user/password-reset/test') !== false;
            }), Argument::cetera())->shouldBeCalled()->willReturn();
        });

        $crawler = $this->client->request('GET', "/password-managing/forgotten");
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());

        $button = $crawler->selectButton('Reset your password');

        $this->client->submit($button->form(), [
            'password_forgotten[email]' => $emailAddress,
        ]);
    }
}
