<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Entity\Client;
use AppBundle\Event\ClientUpdatedEvent;
use AppBundle\EventSubscriber\ClientUpdatedSubscriber;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Time\DateTimeProvider;
use AppBundle\TestHelpers\ClientHelpers;
use AppBundle\TestHelpers\UserHelpers;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class ClientUpdatedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [ClientUpdatedEvent::NAME => 'logEvent'],
            ClientUpdatedSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider clientProvider
     * @test
     */
    public function logEvent(Client $postUpdateClient, string $expectedLogMessage)
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);
        $sut = new ClientUpdatedSubscriber($logger->reveal(), $dateTimeProvider->reveal());

        $preUpdateClient = ClientHelpers::createClient();
        $changedBy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $expectedEvent = [
            'trigger' => $trigger,
            'email_changed_from' => $preUpdateClient->getEmail(),
            'email_changed_to' => $postUpdateClient->getEmail(),
            'changed_on' => $now->format(DateTime::ATOM),
            'changed_by' => $changedBy->getEmail(),
            'subject_full_name' => $postUpdateClient->getFullName(),
            'subject_role' => 'CLIENT',
            'event' => AuditEvents::EVENT_CLIENT_EMAIL_CHANGED,
            'type' => 'audit'
        ];

        $logger->notice($expectedLogMessage, $expectedEvent)->shouldBeCalled();
        $sut->logEvent($event);
    }

    public function clientProvider()
    {
        $postUpdateClient = ClientHelpers::createClient();

        return [
            'Email changed' => [clone $postUpdateClient, ''],
            'Email removed' => [(clone $postUpdateClient)->setEmail(null), 'Client email address removed'],
        ];
    }

    /** @test */
    public function logEvent_only_logs_on_email_change()
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $sut = new ClientUpdatedSubscriber($logger->reveal(), $dateTimeProvider->reveal());

        $preUpdateClient = ClientHelpers::createClient();
        $postUpdateClient = (ClientHelpers::createClient())->setEmail($preUpdateClient->getEmail());
        $changedBy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $logger->notice(Argument::cetera())->shouldNotBeCalled();
        $sut->logEvent($event);
    }
}
