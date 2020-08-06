<?php declare(strict_types=1);

namespace AppBundle\Controller;


use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Logger;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\Service\Time\ClockInterface;
use AppBundle\Service\Time\DateTimeProvider;
use DateTime;
use Prophecy\Argument;

class SettingsControllerTest extends AbstractControllerTestCase
{
    /** @var DateTime */
    private $now, $orderStartDate;

    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->mockLoggedInUser(['ROLE_PROF_ADMIN']);
        $this->now = new DateTime();
        $this->orderStartDate = new DateTime('-1 Day');
    }

    /**
     * @test
     */
    public function profileEditAction(): void
    {
        $this->restClient->put('user/1', $this->user, Argument::type('array'))->shouldBeCalled();
        $this->restClient->get('user/1', Argument::cetera())->shouldBeCalled()->willReturn($this->user);

        $this->injectProphecyService(DateTimeProvider::class, function($dateTimeProvider) {
            $dateTimeProvider->getDateTime()->willReturn($this->now);
        });

        $this->injectProphecyService(Logger::class, function($logger) {
            $expectedEvent = [
                'trigger' => 'DEPUTY_USER',
                'role_changed_from' => 'ROLE_PROF_ADMIN',
                'role_changed_to' => 'ROLE_PROF_TEAM_MEMBER',
                'changed_by' => $this->user->getEmail(),
                'changed_on' => $this->now->format(DateTime::ATOM),
                'user_changed' => $this->user->getEmail(),
                'event' => AuditEvents::EVENT_ROLE_CHANGED,
                'type' => 'audit'
            ];

            $logger->notice('', $expectedEvent)->shouldBeCalled();
        });

        $crawler = $this->client->request('GET', "/org/settings/your-details/edit");
        $button = $crawler->selectButton('Save');

        $this->client->submit($button->form(), [
            'profile[firstname]' => $this->user->getFirstname(),
            'profile[lastname]' => $this->user->getLastname(),
            'profile[phoneMain]' => $this->user->getPhoneMain(),
            'profile[removeAdmin]' => 1,
        ]);
    }
}
