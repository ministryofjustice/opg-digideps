<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Event\UserUpdatedEvent;
use OPG\Digideps\Frontend\EventSubscriber\UserUpdatedSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserUpdatedSubscriberTest extends TestCase
{
    private UserHelpers $userHelpers;
    private DateTimeProvider&MockObject $dateTimeProvider;
    private LoggerInterface&MockObject $logger;
    private Mailer&MockObject $mailer;
    private UserUpdatedSubscriber $sut;

    public function setUp(): void
    {
        $this->userHelpers = new UserHelpers();
        $this->dateTimeProvider = self::createMock(DateTimeProvider::class);
        $this->logger = self::createMock(LoggerInterface::class);
        $this->mailer = self::createMock(Mailer::class);

        $this->sut = (new UserUpdatedSubscriber(
            $this->dateTimeProvider,
            $this->logger,
            $this->mailer
        ));
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [
                UserUpdatedEvent::NAME => [
                    ['auditLog', 2],
                    ['sendEmail', 1],
                ],
            ],
            UserUpdatedSubscriber::getSubscribedEvents()
        );
    }

    public function testAuditLogEmailHasChanged(): void
    {
        $now = new \DateTime('now');

        $preUpdateUser = $this->userHelpers->createUser();
        $postUpdateUser = (clone $preUpdateUser)->setEmail('changed@example.com');
        $currentUser = $this->userHelpers->createUser();
        $trigger = 'A_TRIGGER';

        $expectedEvent = [
            'trigger' => 'A_TRIGGER',
            'email_changed_from' => $preUpdateUser->getEmail(),
            'email_changed_to' => 'changed@example.com',
            'changed_on' => $now->format(\DateTime::ATOM),
            'changed_by' => $currentUser->getEmail(),
            'subject_full_name' => $postUpdateUser->getFullName(),
            'subject_role' => $postUpdateUser->getRoleName(),
            'event' => 'USER_EMAIL_CHANGED',
            'type' => 'audit',
        ];

        $this->dateTimeProvider->expects(self::once())->method('getDateTime')->willReturn($now);
        $this->logger->expects(self::once())->method('notice')->with('', $expectedEvent);

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->auditLog($event);
    }

    public function testAuditLogRoleHasChanged(): void
    {
        $now = new \DateTime('now');

        $preUpdateUser = $this->userHelpers->createUser();
        $postUpdateUser = (clone $preUpdateUser)->setRoleName('A_DIFFERENT_ROLE');
        $currentUser = $this->userHelpers->createUser();
        $trigger = 'A_TRIGGER';

        $expectedEvent = [
            'trigger' => 'A_TRIGGER',
            'role_changed_from' => $preUpdateUser->getRoleName(),
            'role_changed_to' => 'A_DIFFERENT_ROLE',
            'changed_on' => $now->format(\DateTime::ATOM),
            'changed_by' => $currentUser->getEmail(),
            'user_changed' => $postUpdateUser->getEmail(),
            'event' => 'ROLE_CHANGED',
            'type' => 'audit',
        ];

        $this->dateTimeProvider->expects(self::once())->method('getDateTime')->willReturn($now);
        $this->logger->expects(self::once())->method('notice')->with('', $expectedEvent);

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->auditLog($event);
    }

    public function testAuditLogRoleOrEmailHasNotChanged(): void
    {
        $trigger = 'A_TRIGGER';

        $preUpdateUser = $this->userHelpers->createUser();
        $postUpdateUser = (clone $preUpdateUser)->setFirstname('Sufjan')->setLastname('Stevens');
        $currentUser = $this->userHelpers->createUser();

        $this->logger->expects(self::never())->method('notice');

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->auditLog($event);
    }

    /**
     * @dataProvider deputyProvider
     */
    public function testSendEmailLayDeputyDetailsHaveChanged(User $preUpdateUser, User $postUpdateUser): void
    {
        $trigger = 'A_TRIGGER';
        $currentUser = $this->userHelpers->createUser();

        $this->mailer->expects(self::once())->method('sendUpdateDeputyDetailsEmail')->with($postUpdateUser);

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->sendEmail($event);
    }

    public static function deputyProvider(): array
    {
        $preUpdateUser = new User()
            ->setId(1)
            ->setFirstname('Sufjan')
            ->setLastname('Stevens')
            ->setRoleName('ROLE_LAY_DEPUTY')
            ->setEmail('s.stevens@ashmatic-kitty.com')
            ->setAddress1('1 Old Road')
            ->setAddress2('Oldtown')
            ->setAddress3('OldCounty')
            ->setAddressPostcode('B13 2AD')
            ->setAddressCountry('USA')
            ->setPhoneMain('01211234567')
            ->setPhoneAlternative('01213217654');

        return [
            'Firstname changed' => [$preUpdateUser, (clone $preUpdateUser)->setFirstname('Nico')],
            'Lastname changed' => [$preUpdateUser, (clone $preUpdateUser)->setLastname('Muhly')],
            'Address1 changed' => [$preUpdateUser, (clone $preUpdateUser)->setAddress1('1 New Road')],
            'Address2 changed' => [$preUpdateUser, (clone $preUpdateUser)->setAddress2('Newtown')],
            'Address3 changed' => [$preUpdateUser, (clone $preUpdateUser)->setAddress3('Newcounty')],
            'AddressPostCode changed' => [$preUpdateUser, (clone $preUpdateUser)->setAddressPostcode('AB1 C23')],
            'AddressCountry changed' => [$preUpdateUser, (clone $preUpdateUser)->setAddressCountry('GB')],
            'PhoneMain changed' => [$preUpdateUser, (clone $preUpdateUser)->setPhoneMain('0121312341')],
            'PhoneAlternative changed' => [$preUpdateUser, (clone $preUpdateUser)->setPhoneAlternative('01216669999')],
            'Email changed' => [$preUpdateUser, (clone $preUpdateUser)->setEmail('n.muhly@roughtrade.com')],
        ];
    }

    public function testSendEmailEmailNotSentWhenRoleIsNotLayDeputy(): void
    {
        $trigger = 'A_TRIGGER';
        $preUpdateUser = $this->userHelpers->createUser();
        $postUpdateUser = (clone $preUpdateUser)->setRoleName('NOT_LAY_DEPUTY')->setEmail('new.email@example.org');
        $currentUser = $this->userHelpers->createUser();

        $this->mailer->expects(self::never())->method('sendUpdateDeputyDetailsEmail');

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->sendEmail($event);
    }
}
