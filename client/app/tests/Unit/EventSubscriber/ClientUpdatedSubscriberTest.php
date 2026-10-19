<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use Faker\Factory;
use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Event\ClientUpdatedEvent;
use OPG\Digideps\Frontend\EventSubscriber\ClientUpdatedSubscriber;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\ClientHelpers;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClientUpdatedSubscriberTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private DateTimeProvider&MockObject $dateTimeProvider;
    private Mailer&MockObject $mailer;
    private ClientUpdatedSubscriber $sut;

    public function setUp(): void
    {
        $this->logger = self::createMock(LoggerInterface::class);
        $this->dateTimeProvider = self::createMock(DateTimeProvider::class);
        $this->mailer = self::createMock(Mailer::class);

        $this->sut = new ClientUpdatedSubscriber(
            $this->logger,
            $this->dateTimeProvider,
            $this->mailer
        );
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [
                ClientUpdatedEvent::NAME => [
                        ['logEvent', 2],
                        ['sendEmail', 1],
                    ],
            ],
            ClientUpdatedSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider clientProviderLogEvent
     */
    public function testLogEvent(Client $postUpdateClient, string $expectedLogMessage): void
    {
        $now = new \DateTime();
        $this->dateTimeProvider->expects(self::once())->method('getDateTime')->willReturn($now);

        $preUpdateClient = ClientHelpers::createClient();
        $changedBy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $expectedEvent = [
            'trigger' => $trigger,
            'email_changed_from' => $preUpdateClient->getEmail(),
            'email_changed_to' => $postUpdateClient->getEmail(),
            'changed_on' => $now->format(\DateTime::ATOM),
            'changed_by' => $changedBy->getEmail(),
            'subject_full_name' => $postUpdateClient->getFullName(),
            'subject_role' => 'CLIENT',
            'event' => AuditEvents::EVENT_CLIENT_EMAIL_CHANGED,
            'type' => 'audit',
        ];

        $this->logger->expects(self::once())->method('notice')->with($expectedLogMessage, $expectedEvent);

        $this->sut->logEvent($event);
    }

    public static function clientProviderLogEvent(): array
    {
        $postUpdateClient = ClientHelpers::createClient();

        return [
            'Email changed' => [clone $postUpdateClient, ''],
            'Email removed' => [(clone $postUpdateClient)->setEmail(null), 'Client email address removed'],
        ];
    }

    public function testLogEventOnlyLogsOnEmailChange(): void
    {
        $preUpdateClient = ClientHelpers::createClient();
        $postUpdateClient = ClientHelpers::createClient()->setEmail($preUpdateClient->getEmail());
        $changedBy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $this->logger->expects(self::never())->method('notice');

        $this->sut->logEvent($event);
    }

    /**
     * @dataProvider clientProviderSendEmailDetailsChanged
     */
    public function testSendEmail(Client $preUpdateClient, Client $postUpdateClient): void
    {
        $changedBy = UserHelpers::createUser()->setRoleName(User::ROLE_LAY_DEPUTY);
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $this->mailer->expects(self::once())->method('sendUpdateClientDetailsEmail')->with($postUpdateClient);

        $this->sut->sendEmail($event);
    }

    public static function clientProviderSendEmailDetailsChanged(): array
    {
        $faker = Factory::create('GB_en');

        $preUpdateClient = ClientHelpers::createClient();

        return [
            'Firstname changed' => [$preUpdateClient, (clone $preUpdateClient)->setFirstname($faker->firstName())],
            'Lastname changed' => [$preUpdateClient, (clone $preUpdateClient)->setLastname($faker->lastName())],
            'Address changed' => [$preUpdateClient, (clone $preUpdateClient)->setAddress($faker->address())],
            'Address2 changed' => [$preUpdateClient, (clone $preUpdateClient)->setAddress2($faker->address())],
            'CourtDate changed' => [$preUpdateClient, (clone $preUpdateClient)->setCourtDate(new \DateTime($faker->date()))],
            'Postcode changed' => [$preUpdateClient, (clone $preUpdateClient)->setPostcode($faker->postcode())],
            'Country changed' => [$preUpdateClient, (clone $preUpdateClient)->setCountry('USA')],
            'Phone changed' => [$preUpdateClient, (clone $preUpdateClient)->setPhone($faker->phoneNumber())],
            'Email changed' => [$preUpdateClient, (clone $preUpdateClient)->setEmail($faker->email())],
        ];
    }

    public function testSendEmailClientDetailsNotChanged(): void
    {
        $preUpdateClient = ClientHelpers::createClient();
        $postUpdateClient = clone $preUpdateClient;
        $changedBy = UserHelpers::createUser()->setRoleName(User::ROLE_LAY_DEPUTY);
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $this->mailer->expects(self::never())->method('sendUpdateClientDetailsEmail');

        $this->sut->sendEmail($event);
    }

    public function testSendEmailEmailNotSentWhenDetailsChangedButClientsAreDifferent(): void
    {
        $preUpdateClient = ClientHelpers::createClient();
        $postUpdateClient = ClientHelpers::createClient()->setId(12345);
        $changedBy = UserHelpers::createUser()->setRoleName(User::ROLE_LAY_DEPUTY);
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $this->mailer->expects(self::never())->method('sendUpdateClientDetailsEmail');

        $this->sut->sendEmail($event);
    }
}
