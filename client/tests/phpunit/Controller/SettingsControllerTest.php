<?php declare(strict_types=1);

namespace AppBundle\Controller;


use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Logger;
use AppBundle\Service\Time\DateTimeProvider;
use DateTime;
use Prophecy\Argument;

class SettingsControllerTest extends AbstractControllerTestCase
{
    /** @var DateTime */
    private $now;

    public function setUp(): void
    {
        parent::setUp();

        $this->now = new DateTime();
    }

    /**
     * @test
     */
    public function profileEditAction_user_role_change_is_logged(): void
    {
        $profAdminUser = $this->mockLoggedInUser(['ROLE_PROF_ADMIN']);

        $this->restClient->put('user/1', $profAdminUser, Argument::type('array'))->shouldBeCalled();
        $this->restClient->get('user/1', Argument::cetera())->shouldBeCalled()->willReturn($profAdminUser);

        $this->injectProphecyService(DateTimeProvider::class, function($dateTimeProvider) {
            $dateTimeProvider->getDateTime()->willReturn($this->now);
        });

        $this->injectProphecyService(Logger::class, function($logger) use($profAdminUser) {
            $expectedEvent = [
                'trigger' => 'DEPUTY_USER',
                'role_changed_from' => 'ROLE_PROF_ADMIN',
                'role_changed_to' => 'ROLE_PROF_TEAM_MEMBER',
                'changed_by' => $profAdminUser->getEmail(),
                'changed_on' => $this->now->format(DateTime::ATOM),
                'user_changed' => $profAdminUser->getEmail(),
                'event' => AuditEvents::EVENT_ROLE_CHANGED,
                'type' => 'audit'
            ];

            $logger->notice('', $expectedEvent)->shouldBeCalled();
        });

        $crawler = $this->client->request('GET', "/org/settings/your-details/edit");
        $button = $crawler->selectButton('Save');

        $this->client->submit($button->form(), [
            'profile[firstname]' => $profAdminUser->getFirstname(),
            'profile[lastname]' => $profAdminUser->getLastname(),
            'profile[phoneMain]' => $profAdminUser->getPhoneMain(),
            'profile[removeAdmin]' => 1,
        ]);
    }

    /**
     * @test
     */
    public function profileEditAction_lay_deputy_change_email_is_logged(): void
    {
        $layDeputyUser = $this->mockLoggedInUser(['ROLE_LAY_DEPUTY']);

        $this->restClient->put('user/1', $layDeputyUser, Argument::type('array'))->shouldBeCalled();
        $this->restClient->get('user/1', Argument::cetera())->shouldBeCalled()->willReturn($layDeputyUser);

        $this->injectProphecyService(DateTimeProvider::class, function($dateTimeProvider) {
            $dateTimeProvider->getDateTime()->willReturn($this->now);
        });

        $this->injectProphecyService(Logger::class, function($logger) use($layDeputyUser) {
            $expectedEvent = [
                'trigger' => 'DEPUTY_USER',
                'email_changed_from' => 'logged-in-user@email.com',
                'email_changed_to' => 'i-have-changed@email.com',
                'changed_on' => $this->now->format(DateTime::ATOM),
                'changed_by' => 'i-have-changed@email.com',
                'subject_full_name' => $layDeputyUser->getFullName(),
                'subject_role' => 'ROLE_LAY_DEPUTY',
                'event' => 'USER_EMAIL_CHANGED',
                'type' => 'audit'
            ];

            $logger->notice('', $expectedEvent)->shouldBeCalled();
        });

        $crawler = $this->client->request('GET', "/org/settings/your-details/edit");
        $button = $crawler->selectButton('Save');

        $this->client->submit($button->form(), [
            'profile[firstname]' => $layDeputyUser->getFirstname(),
            'profile[lastname]' => $layDeputyUser->getLastname(),
            'profile[addressCountry]' => $layDeputyUser->getAddressCountry(),
            'profile[email]' => 'i-have-changed@email.com'
        ]);
    }
}
