<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Entity\User;
use AppBundle\Event\UserUpdatedEvent;
use AppBundle\EventSubscriber\UserUpdatedSubscriber;
use AppBundle\Model\Email;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\Service\Time\DateTimeProvider;
use AppBundle\TestHelpers\UserHelpers;
use DateTime;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserUpdatedSubscriberTest extends KernelTestCase
{
    /** @var UserHelpers */
    private $userHelpers;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $dateTimeProvider;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $logger;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $mailFactory;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $mailSender;

    /** @var UserUpdatedListener */
    private $sut;

    public function setUp(): void
    {
        $container = (self::bootKernel())->getContainer();

        $this->userHelpers = new UserHelpers($container->get('serializer'));
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->mailFactory = self::prophesize(MailFactory::class);
        $this->mailSender = self::prophesize(MailSender::class);

        $this->sut = new UserUpdatedSubscriber(
            $this->dateTimeProvider->reveal(),
            $this->logger->reveal(),
            $this->mailFactory->reveal(),
            $this->mailSender->reveal()
        );
    }

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [
                UserUpdatedEvent::NAME => 'auditLog',
                UserUpdatedEvent::NAME => 'sendEmail'
            ],
            UserUpdatedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function auditLog_email_has_changed()
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
            'type' => 'audit'
        ];

        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($now);
        $this->logger->notice('', $expectedEvent)->shouldBeCalled();

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->auditLog($event);
    }

    /** @test */
    public function auditLog_role_has_changed()
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
            'type' => 'audit'
        ];

        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($now);
        $this->logger->notice('', $expectedEvent)->shouldBeCalled();

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->auditLog($event);
    }

    /** @test */
    public function auditLog_role_or_email_has_not_changed()
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
    public function sendEmail_lay_deputy_details_have_changed(User $preUpdateUser, User $postUpdateUser)
    {
        $trigger = 'A_TRIGGER';
        $currentUser = $this->userHelpers->createUser();

        $this->mailFactory->createUpdateDeputyDetailsEmail($postUpdateUser)->shouldBeCalled();
        $this->mailSender->send(Argument::type(Email::class))->shouldBeCalled();

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
    public function sendEmail_email_not_sent_when_role_is_not_lay_deputy()
    {
        $trigger = 'A_TRIGGER';
        $preUpdateUser = $this->userHelpers->createUser();
        $postUpdateUser = (clone $preUpdateUser)->setRoleName('NOT_LAY_DEPUTY')->setEmail('new.email@example.org');
        $currentUser = $this->userHelpers->createUser();

        $this->mailFactory->createUpdateDeputyDetailsEmail(Argument::any())->shouldNotBeCalled();
        $this->mailSender->send(Argument::any())->shouldNotBeCalled();

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->sut->sendEmail($event);
    }
}
