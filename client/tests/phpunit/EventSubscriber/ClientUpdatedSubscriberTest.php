<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Entity\Client;
use App\Entity\User;
use App\Event\ClientUpdatedEvent;
use App\EventSubscriber\ClientUpdatedSubscriber;
use App\Service\Audit\AuditEvents;
use App\Service\Mailer\Mailer;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\ClientHelpers;
use App\TestHelpers\UserHelpers;
use DateTime;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class ClientUpdatedSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    private $logger;

    /** @var ObjectProphecy */
    private $dateTimeProvider;

    /** @var ObjectProphecy */
    private $mailer;

    /** @var ClientUpdatedSubscriber */
    private $sut;

    public function setUp(): void
    {
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->mailer = self::prophesize(Mailer::class);

        $this->sut = new ClientUpdatedSubscriber(
            $this->logger->reveal(),
            $this->dateTimeProvider->reveal(),
            $this->mailer->reveal()
        );
    }

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [ClientUpdatedEvent::NAME => 'logEvent', ClientUpdatedEvent::NAME => 'sendEmail'],
            ClientUpdatedSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider clientProvider_logEvent
     * @test
     */
    public function logEvent(Client $postUpdateClient, string $expectedLogMessage)
    {
        $now = new DateTime();
        $this->dateTimeProvider->getDateTime()->willReturn($now);

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
            'type' => 'audit',
        ];

        $this->logger->notice($expectedLogMessage, $expectedEvent)->shouldBeCalled();
        $this->sut->logEvent($event);
    }

    public function clientProvider_logEvent()
    {
        $postUpdateClient = ClientHelpers::createClient();

        return [
            'Email changed' => [clone $postUpdateClient, ''],
            'Email removed' => [(clone $postUpdateClient)->setEmail(null), 'Client email address removed'],
        ];
    }

    /** @test */
    public function logEventOnlyLogsOnEmailChange()
    {
        $preUpdateClient = ClientHelpers::createClient();
        $postUpdateClient = (ClientHelpers::createClient())->setEmail($preUpdateClient->getEmail());
        $changedBy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $this->logger->notice(Argument::cetera())->shouldNotBeCalled();
        $this->sut->logEvent($event);
    }

    /**
     * @test
     * @dataProvider clientProvider_sendEmail_details_changed
     */
    public function sendEmail(Client $preUpdateClient, Client $postUpdateClient)
    {
        $changedBy = (UserHelpers::createUser())->setRoleName(User::ROLE_LAY_DEPUTY);
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $this->mailer->sendUpdateClientDetailsEmail($postUpdateClient)->shouldBeCalled();
        $this->sut->sendEmail($event);
    }

    public function clientProvider_sendEmail_details_changed()
    {
        $faker = Factory::create('GB_en');

        $preUpdateClient = ClientHelpers::createClient();

        return [
            'Firstname changed' => [$preUpdateClient, (clone $preUpdateClient)->setFirstname($faker->firstName())],
            'Lastname changed' => [$preUpdateClient, (clone $preUpdateClient)->setLastname($faker->lastName())],
            'Address changed' => [$preUpdateClient, (clone $preUpdateClient)->setAddress($faker->address())],
            'Address2 changed' => [$preUpdateClient, (clone $preUpdateClient)->setAddress2($faker->address())],
            'CourtDate changed' => [$preUpdateClient, (clone $preUpdateClient)->setCourtDate(new DateTime($faker->date()))],
            'County changed' => [$preUpdateClient, (clone $preUpdateClient)->setCounty($faker->state())],
            'Postcode changed' => [$preUpdateClient, (clone $preUpdateClient)->setPostcode($faker->postcode())],
            'Country changed' => [$preUpdateClient, (clone $preUpdateClient)->setCountry('USA')],
            'Phone changed' => [$preUpdateClient, (clone $preUpdateClient)->setPhone($faker->phoneNumber())],
            'Email changed' => [$preUpdateClient, (clone $preUpdateClient)->setEmail($faker->email())],
        ];
    }

    /** @test */
    public function sendEmailClientDetailsNotChanged()
    {
        $preUpdateClient = ClientHelpers::createClient();
        $postUpdateClient = clone $preUpdateClient;
        $changedBy = (UserHelpers::createUser())->setRoleName(User::ROLE_LAY_DEPUTY);
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $this->mailer->sendUpdateClientDetailsEmail($postUpdateClient)->shouldNotBeCalled();
        $this->sut->sendEmail($event);
    }

    /** @test */
    public function sendEmailEmailNotSentWhenDetailsChangedButClientsAreDifferent()
    {
        $preUpdateClient = ClientHelpers::createClient();
        $postUpdateClient = (ClientHelpers::createClient())->setId(12345);
        $changedBy = (UserHelpers::createUser())->setRoleName(User::ROLE_LAY_DEPUTY);
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        $this->mailer->sendUpdateClientDetailsEmail($postUpdateClient)->shouldNotBeCalled();
        $this->sut->sendEmail($event);
    }
}
