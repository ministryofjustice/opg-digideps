<?php

declare(strict_types=1);

use App\Entity\Client;
use App\Event\ClientDeletedEvent;
use App\EventSubscriber\ClientDeletedSubscriber;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\ClientHelpers;
use App\TestHelpers\DeputyHelper;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class ClientDeletedSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals([
            ClientDeletedEvent::NAME => 'logEvent',
        ], ClientDeletedSubscriber::getSubscribedEvents());
    }

    /**
     * @dataProvider deputyProvider
     *
     * @test
     */
    public function logEvent(Client $clientWithUsers, $deputy)
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);
        $sut = new ClientDeletedSubscriber($logger->reveal(), $dateTimeProvider->reveal());

        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $clientDeletedEvent = new ClientDeletedEvent($clientWithUsers, $currentUser, $trigger);

        $expectedEvent = [
            'trigger' => $trigger,
            'case_number' => $clientWithUsers->getCaseNumber(),
            'discharged_by' => $currentUser->getEmail(),
            'deputy_name' => $deputy->getFullName(),
            'discharged_on' => $now->format(DateTime::ATOM),
            'deputyship_start_date' => $clientWithUsers->getCourtDate()->format(DateTime::ATOM),
            'event' => AuditEvents::EVENT_CLIENT_DELETED,
            'type' => 'audit',
        ];

        $logger->notice('', $expectedEvent)->shouldBeCalled();
        $sut->logEvent($clientDeletedEvent);
    }

    public function deputyProvider()
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
