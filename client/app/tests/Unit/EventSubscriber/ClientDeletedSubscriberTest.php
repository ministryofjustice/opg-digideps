<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Event\ClientDeletedEvent;
use OPG\Digideps\Frontend\EventSubscriber\ClientDeletedSubscriber;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\ClientHelpers;
use OPG\Digideps\Frontend\TestHelpers\DeputyHelper;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClientDeletedSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals([
            ClientDeletedEvent::NAME => 'logEvent',
        ], ClientDeletedSubscriber::getSubscribedEvents());
    }

    /**
     * @dataProvider deputyProvider
     */
    public function testLogEvent(Client $clientWithUsers, $deputy): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $dateTimeProvider = self::createMock(DateTimeProvider::class);

        $now = new \DateTime();
        $dateTimeProvider->expects(self::once())->method('getDateTime')->willReturn($now);

        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $clientDeletedEvent = new ClientDeletedEvent($clientWithUsers, $currentUser, $trigger);

        $expectedEvent = [
            'trigger' => $trigger,
            'case_number' => $clientWithUsers->getCaseNumber(),
            'discharged_by' => $currentUser->getEmail(),
            'deputy_name' => $deputy->getFullName(),
            'discharged_on' => $now->format(\DateTime::ATOM),
            'deputyship_start_date' => $clientWithUsers->getCourtDate()->format(\DateTime::ATOM),
            'event' => AuditEvents::EVENT_CLIENT_DELETED,
            'type' => 'audit',
        ];

        $logger->expects(self::once())->method('notice')->with('', $expectedEvent);

        new ClientDeletedSubscriber($logger, $dateTimeProvider)->logEvent($clientDeletedEvent);
    }

    public static function deputyProvider(): array
    {
        $clientWithUsers = ClientHelpers::createClient();
        $layDeputy = UserHelpers::createUser()->setRoleName('ROLE_LAY_DEPUTY');
        $deputy = DeputyHelper::createDeputy();

        return [
            'Lay deputy' => [(clone $clientWithUsers)->addUser($layDeputy), $layDeputy],
            'Deputy' => [(clone $clientWithUsers)->setDeputy($deputy), $deputy],
        ];
    }
}
