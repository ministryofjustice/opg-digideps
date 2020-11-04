<?php declare(strict_types=1);


use AppBundle\Event\ClientDeletedEvent;
use AppBundle\EventSubscriber\ClientDeletedSubscriber;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Time\DateTimeProvider;
use AppBundle\TestHelpers\ClientHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClientDeletedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals([
            ClientDeletedEvent::NAME => 'logEvent'
        ], ClientDeletedSubscriber::getSubscribedEvents());
    }

    /** @test */
    public function logEvent()
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);
        $sut = new ClientDeletedSubscriber($logger->reveal(), new AuditEvents($dateTimeProvider->reveal()));

        $client = ClientHelpers::createClient();
        $currentUser = UserHelpers::createUser();
        $deputy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $clientDeletedEvent = new ClientDeletedEvent($client, $currentUser, $deputy, $trigger);

        $expectedEvent = [
            'trigger' => $trigger,
            'case_number' => $client->getCaseNumber(),
            'discharged_by' => $currentUser->getEmail(),
            'deputy_name' => $deputy->getFullName(),
            'discharged_on' => $now->format(DateTime::ATOM),
            'deputyship_start_date' => $client->getCourtDate()->format(DateTime::ATOM),
            'event' => AuditEvents::EVENT_CLIENT_DISCHARGED,
            'type' => 'audit'
        ];
        ;

        $logger->notice('', $expectedEvent)->shouldBeCalled();
        $sut->logEvent($clientDeletedEvent);
    }
}
