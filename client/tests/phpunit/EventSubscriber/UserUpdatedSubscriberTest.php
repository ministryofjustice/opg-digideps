<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Entity\User;
use App\Event\UserUpdatedEvent;
use App\EventSubscriber\UserUpdatedSubscriber;
use App\Service\Mailer\Mailer;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\UserHelpers;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class UserUpdatedSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @var UserHelpers */
    private $userHelpers;

    /** @var ObjectProphecy */
    private $dateTimeProvider;

    /** @var ObjectProphecy */
    private $logger;

    /** @var ObjectProphecy */
    private $mailer;

    /** @var UserUpdatedSubscriber */
    private $sut;

    public function setUp(): void
    {
        $this->userHelpers = new UserHelpers();
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->mailer = self::prophesize(Mailer::class);

        $this->sut = (new UserUpdatedSubscriber(
            $this->dateTimeProvider->reveal(),
            $this->logger->reveal(),
            $this->mailer->reveal()
        ));
    }

    /** @test */
    public function getSubscribedEvents()
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

    /** @test */
    public function auditLogEmailHasChanged()
    {
        $now = new DateTime('now');

        $preUpdateUser = $this->userHelpers->createUser();
        $postUpdateUser = (clone $preUpdateUser)->setEmail('changed@example.com');
        $currentUser = $this->userHelpers->createUser();
        $trigger = 'A_TRIGGER';

        $expectedEvent = [
            'trigger' => 'A_TRIGGER',
            'email_changed_from' => $preUpdateUser->getEmail(),
            'email_changed_to' => 'changed@example.com',
            'changed_on' => $now->format(DateTime::ATOM),
            'changed_by' => $currentUser->getEmail(),
            'subject_full_name' => $postUpdateUser->getFullName(),
            'subject_role' => $postUpdateUser->getRoleName(),
            'event' => 'USER_EMAIL_CHANGED',
            'type' => 'audit',
        ];

        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($now);
        $this->logger->notice('', $expectedEvent)->shouldBeCalled();

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->auditLog($event);
    }

    /** @test */
    public function auditLogRoleHasChanged()
    {
        $now = new DateTime('now');

        $preUpdateUser = $this->userHelpers->createUser();
        $postUpdateUser = (clone $preUpdateUser)->setRoleName('A_DIFFERENT_ROLE');
        $currentUser = $this->userHelpers->createUser();
        $trigger = 'A_TRIGGER';

        $expectedEvent = [
            'trigger' => 'A_TRIGGER',
            'role_changed_from' => $preUpdateUser->getRoleName(),
            'role_changed_to' => 'A_DIFFERENT_ROLE',
            'changed_on' => $now->format(DateTime::ATOM),
            'changed_by' => $currentUser->getEmail(),
            'user_changed' => $postUpdateUser->getEmail(),
            'event' => 'ROLE_CHANGED',
            'type' => 'audit',
        ];

        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($now);
        $this->logger->notice('', $expectedEvent)->shouldBeCalled();

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->auditLog($event);
    }

    /** @test */
    public function auditLogRoleOrEmailHasNotChanged()
    {
        $trigger = 'A_TRIGGER';

        $preUpdateUser = $this->userHelpers->createUser();
        $postUpdateUser = (clone $preUpdateUser)->setFirstname('Sufjan')->setLastname('Stevens');
        $currentUser = $this->userHelpers->createUser();

        $this->logger->notice(Argument::cetera())->shouldNotBeCalled();

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->auditLog($event);
    }

    /**
     * @dataProvider deputyProvider
     * @test
     */
    public function sendEmailLayDeputyDetailsHaveChanged(User $preUpdateUser, User $postUpdateUser)
    {
        $trigger = 'A_TRIGGER';
        $currentUser = $this->userHelpers->createUser();

        $this->mailer->sendUpdateDeputyDetailsEmail($postUpdateUser)->shouldBeCalled();

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->sendEmail($event);
    }

    public function deputyProvider()
    {
        $preUpdateUser = (new User())
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

    /**
     * @test
     */
    public function sendEmailEmailNotSentWhenRoleIsNotLayDeputy()
    {
        $trigger = 'A_TRIGGER';
        $preUpdateUser = $this->userHelpers->createUser();
        $postUpdateUser = (clone $preUpdateUser)->setRoleName('NOT_LAY_DEPUTY')->setEmail('new.email@example.org');
        $currentUser = $this->userHelpers->createUser();

        $this->mailer->sendUpdateDeputyDetailsEmail(Argument::any())->shouldNotBeCalled();

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->sendEmail($event);
    }
}
