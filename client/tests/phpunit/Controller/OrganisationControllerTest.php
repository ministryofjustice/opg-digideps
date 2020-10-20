<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Logger;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\Service\Time\DateTimeProvider;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

class OrganisationControllerTest extends AbstractControllerTestCase
{
    /** @var User */
    private $user;

    /** @var DateTime */
    private $now;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->mockLoggedInUser(['ROLE_PROF_ADMIN']);
        $this->now = new DateTime();
    }

    public function testAddAction(): void
    {

        $emailAddress = 'invited@mailbox.example';
        $user = (new User())
            ->setId(21)
            ->setEmail($emailAddress)
            ->setRegistrationToken('invitation-token');

        $organisation = (new Organisation())
            ->setId(14)
            ->setName('Test organisation');

        $this->restClient->get('v2/organisation/14', 'Organisation')->shouldBeCalled()->willReturn($organisation);
        $this->restClient->get("user/get-team-names-by-email/$emailAddress", 'User')->shouldBeCalled()->willReturn(new User());
        $this->restClient->post('user', Argument::any(), ['org_team_add'], 'User')->shouldBeCalled()->willReturn($user);
        $this->restClient->put('v2/organisation/14/user/21', '')->shouldBeCalled()->willReturn($user);

        $crawler = $this->client->request('GET', "/org/settings/organisation/14/add-user");
        $button = $crawler->selectButton('Save');

        $this->client->submit($button->form(), [
            'organisation_member[firstname]' => 'Aron',
            'organisation_member[lastname]' => 'Samora',
            'organisation_member[email]' => $emailAddress,
            'organisation_member[roleName]' => 'ROLE_PROF_ADMIN',
        ]);

        /** @var Response $response */
        $response = $this->client->getResponse();

        self::assertEquals(302, $response->getStatusCode());
    }

    public function testAddActionInsertsExistingUsers(): void
    {
        $user = (new User())
            ->setId(21)
            ->setEmail('existing@mailbox.example')
            ->setRegistrationToken('invitation-token');

        $organisation = (new Organisation())
            ->setId(14)
            ->setName('Test organisation');

        $this->restClient->get('v2/organisation/14', 'Organisation')->shouldBeCalled()->willReturn($organisation);
        $this->restClient->get("user/get-team-names-by-email/existing@mailbox.example", 'User')->shouldBeCalled()->willReturn($user);
        $this->restClient->put('v2/organisation/14/user/21', '')->shouldBeCalled()->willReturn($user);

        $crawler = $this->client->request('GET', "/org/settings/organisation/14/add-user");
        $button = $crawler->selectButton('Save');

        $this->client->submit($button->form(), [
            'organisation_member[firstname]' => 'Aron',
            'organisation_member[lastname]' => 'Samora',
            'organisation_member[email]' => 'existing@mailbox.example',
            'organisation_member[roleName]' => 'ROLE_PROF_ADMIN',
        ]);

        /** @var Response $response */
        $response = $this->client->getResponse();

        self::assertEquals(302, $response->getStatusCode());
    }

    public function testResendActivationEmailAction(): void
    {
        $emailAddress = 'invited@mailbox.example';
        $invitedUser = (new User())
            ->setId(17)
            ->setEmail($emailAddress)
            ->setRegistrationToken('invitation-token');

        $organisation = (new Organisation())
            ->setId(14)
            ->setName('Test organisation')
            ->setUsers([$invitedUser]);

        $this->restClient->get('v2/organisation/14', 'Organisation')->shouldBeCalled()->willReturn($organisation);
        $this->restClient->userRecreateToken($emailAddress, 'pass-reset')->shouldBeCalled()->willReturn($invitedUser);

        $this->client->request('GET', "/org/settings/organisation/14/send-activation-link/17");
        $this->client->followRedirect();

        /** @var Response $response */
        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertIsString($response->getContent());
        self::assertStringContainsString('An activation email has been sent to the user', $response->getContent());
    }

    /** @test */
    public function editAction()
    {
        $organisation = (new Organisation())
            ->setId(1);

        $editedUser = (new User())
            ->setId(2)
            ->setFirstname('Laura')
            ->setLastname('Veirs')
            ->setEmail('l.veirs@test.com')
            ->setRoleName('ROLE_PROF_ADMIN')
            ->setOrganisations(new ArrayCollection([$organisation]));

        $organisation->setUsers([$editedUser]);

        $this->restClient->get(sprintf('v2/organisation/%s', $organisation->getId()), 'Organisation')->shouldBeCalled()->willReturn($organisation);
        $this->restClient->put(sprintf('user/%s', $editedUser->getId()), $editedUser, ['org_team_add'])->shouldBeCalled();

        $this->injectProphecyService(DateTimeProvider::class, function($dateTimeProvider) {
            $dateTimeProvider->getDateTime()->willReturn($this->now);
        });

        $this->injectProphecyService(Logger::class, function($logger) use ($editedUser) {
            $expectedEvent = [
                'trigger' => 'DEPUTY_USER',
                'role_changed_from' => 'ROLE_PROF_ADMIN',
                'role_changed_to' => 'ROLE_PROF_TEAM_MEMBER',
                'changed_by' => $this->user->getEmail(),
                'changed_on' => $this->now->format(DateTime::ATOM),
                'user_changed' => $editedUser->getEmail(),
                'event' => AuditEvents::EVENT_ROLE_CHANGED,
                'type' => 'audit'
            ];

            $logger->notice('', $expectedEvent)->shouldBeCalled();
        });

        $crawler = $this->client->request('GET', sprintf("org/settings/organisation/%s/edit/%s", $editedUser->getOrganisations()[0]->getId(), $editedUser->getId()));
        $button = $crawler->selectButton('Save');

        $this->client->submit($button->form(), [
            'organisation_member[firstname]' => 'Laura',
            'organisation_member[lastname]' => 'Veirs',
            'organisation_member[email]' => 'l.veirs@test.com',
            'organisation_member[roleName]' => 'ROLE_PROF_TEAM_MEMBER',
        ]);
    }
}
