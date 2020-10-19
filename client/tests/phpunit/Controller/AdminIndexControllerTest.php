<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Logger;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\Service\Time\DateTimeProvider;
use DateTime;
use Exception;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminIndexControllerTest extends AbstractControllerTestCase
{
    /** @var DateTime */
    private $now;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockLoggedInUser(['ROLE_SUPER_ADMIN']);
        $this->now = new DateTime();
    }

    public function testAddUserAction(): void
    {
        $this->restClient->post('user', Argument::any(), ['admin_add_user'], 'User')->shouldBeCalled()->willReturn(new User());

        $this->injectProphecyService(MailFactory::class, function ($mailFactory) {
            $mailFactory->createActivationEmail(new User())->shouldBeCalled()->willReturn(new Email());
        });

        $this->injectProphecyService(MailSender::class, function ($mailSender) {
            $mailSender->send(new Email())->shouldBeCalled()->willReturn(true);
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
            $mailSender->send(new Email())->shouldBeCalled()->willReturn(true);
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

    /**
     * @test
     */
    public function deleteAction_audit_log_created(): void
    {
        $deputy = (new User())
            ->setId(5)
            ->setRoleName('ROLE_LAY_DEPUTY')
            ->setFirstname('Roisin')
            ->setLastname('Murphy')
            ->setEmail('r.murphy@email.com');

        $this->restClient->get(sprintf('user/%s', $deputy->getId()), Argument::cetera())->shouldBeCalled()->willReturn($deputy);
        $this->restClient->delete(sprintf('user/%s', $deputy->getId()))->shouldBeCalled();

        $this->injectProphecyService(Logger::class, function ($logger) use ($deputy) {
            $expectedEvent = [
                'trigger' => 'ADMIN_BUTTON',
                'deleted_on' => $this->now->format(DateTime::ATOM),
                'deleted_by' => 'logged-in-user@email.com',
                'subject_full_name' => $deputy->getFullName(),
                'subject_email' => $deputy->getEmail(),
                'subject_role' => 'ROLE_LAY_DEPUTY',
                'event' => 'DEPUTY_DELETED',
                'type' => 'audit'
            ];

            $logger->notice('', $expectedEvent)->shouldBeCalled();
        });

        $crawler = $this->client->request('GET', sprintf("/admin/delete-confirm/%s", $deputy->getId()));
        $deleteLink = $crawler->selectLink("Yes, I'm sure")->link();

        $this->client->click($deleteLink);
    }

    /**
     * @test
     */
    public function deleteAction_errors_logged(): void
    {
        $deputy = (new User())
            ->setId(5)
            ->setRoleName('ROLE_LAY_DEPUTY')
            ->setFirstname('Roisin')
            ->setLastname('Murphy')
            ->setEmail('r.murphy@email.com');

        $this->restClient->get(sprintf('user/%s', $deputy->getId()), Argument::cetera())->shouldBeCalled()->willReturn($deputy);
        $this->restClient->delete(sprintf('user/%s', $deputy->getId()))->shouldBeCalled()->willThrow(new Exception('Something went wrong'));

        $this->injectProphecyService(Logger::class, function ($logger) {
            $logger->notice(Argument::cetera())->shouldNotBeCalled();
            $logger->warning('Error while deleting deputy: Something went wrong', ['deputy_email' => 'r.murphy@email.com'])->shouldBeCalled();
        });

        $crawler = $this->client->request('GET', sprintf("/admin/delete-confirm/%s", $deputy->getId()));
        $deleteLink = $crawler->selectLink("Yes, I'm sure")->link();

        $this->client->click($deleteLink);

        $session = $this->client->getContainer()->get('session');
        self::assertContains("There was a problem deleting the deputy - please try again later", $session->getBag('flashes')->peek('error'));
    }
}
