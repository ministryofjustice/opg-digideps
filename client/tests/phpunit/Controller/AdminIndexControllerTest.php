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

        $this->injectProphecyService(MailFactory::class, function($mailFactory) {
            $mailFactory->createActivationEmail(new User())->shouldBeCalled()->willReturn(new Email());
        });

        $this->injectProphecyService(MailSender::class, function($mailSender) {
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
    public function editUserAction_user_email_changed_audit_log_created(): void
    {
        $userDeputyJustRole = (new User())
            ->setId(5)
            ->setRoleName('ROLE_LAY_DEPUTY');

        $userDeputyAllDetails = (clone $userDeputyJustRole)
            ->setFirstname('Panda')
            ->setLastname('Bear')
            ->setEmail('p.bear@email.com')
            ->setAddressPostcode('B31 2AB');

        $userDeputyUpdated = (clone $userDeputyAllDetails)
            ->setEmail('panda.bear@email.com');

        $this->restClient->get(sprintf('user/%s', $userDeputyJustRole->getId()), Argument::cetera())->shouldBeCalled()->willReturn($userDeputyJustRole);
        $this->restClient->get(sprintf('user/%s', $userDeputyAllDetails->getId()), Argument::cetera())->shouldBeCalled()->willReturn($userDeputyAllDetails);
        $this->restClient->put(sprintf('user/%s', $userDeputyUpdated->getId()), Argument::cetera())->shouldBeCalled()->willReturn($userDeputyUpdated);

        $this->injectProphecyService(DateTimeProvider::class, function($dateTimeProvider) {
            $dateTimeProvider->getDateTime()->willReturn($this->now);
        });

        $this->injectProphecyService(Logger::class, function($logger) use($userDeputyUpdated) {
            $expectedEvent = [
                'trigger' => 'ADMIN_USER_EDIT',
                'email_changed_from' => 'p.bear@email.com',
                'email_changed_to' => 'panda.bear@email.com',
                'full_name' => $userDeputyUpdated->getFullName(),
                'changed_on' => $this->now->format(DateTime::ATOM),
                'changed_by' => 'logged-in-user@email.com',
                'subject_role' => 'ROLE_LAY_DEPUTY',
                'event' => 'USER_EMAIL_CHANGED',
                'type' => 'audit'
            ];

            $logger->notice('', $expectedEvent)->shouldBeCalled();
        });

        $crawler = $this->client->request('GET', sprintf("/admin/edit-user?filter=%s", $userDeputyJustRole->getId()));
        $button = $crawler->selectButton('Update user');

        $this->client->submit($button->form(), [
            'admin[firstname]' => 'Panda',
            'admin[lastname]' => 'Bear',
            'admin[email]' => 'panda.bear@email.com',
            'admin[addressPostcode]' => 'B31 2AB'
        ]);
    }
}
