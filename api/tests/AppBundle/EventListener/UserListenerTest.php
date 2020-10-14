<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Entity\User;
use AppBundle\EventListener\UserListener;
use AppBundle\Service\Time\DateTimeProvider;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;

class UserListenerTest extends TestCase
{
    /** @var DateTime */
    private $now;

    /** @var User */
    private $originalUser;
    private $loggedInUser;

    /** @var array */
    private $expectedLogEvent;

    /** @var ObjectProphecy|EntityManager */
    private $em;

    /** @var ObjectProphecy|LoggerInterface */
    private $logger;

    /** @var ObjectProphecy|Security  */
    private $security;

    /** @var ObjectProphecy|DateTimeProvider */
    private $dateProvider;

    public function setUp(): void
    {
        $this->now = new DateTime();

        $this->originalUser = (new User())
            ->setRoleName('ROLE_LAY_DEPUTY')
            ->setFirstname('Panda')
            ->setLastname('Bear')
            ->setEmail('p.bear@email.com')
            ->setAddressPostcode('B31 2AB');

        $this->loggedInUser = (new User())
            ->setEmail('logged-in-user@email.com');

        $this->expectedLogEvent = [
            'trigger' => 'ADMIN_USER_EDIT',
            'email_changed_from' => 'p.bear@email.com',
            'email_changed_to' => 'panda.bear@email.com',
            'changed_on' => $this->now->format(DateTime::ATOM),
            'changed_by' => 'logged-in-user@email.com',
            'subject_full_name' => $this->originalUser->getFullName(),
            'subject_role' => 'ROLE_LAY_DEPUTY',
            'event' => 'USER_EMAIL_CHANGED',
            'type' => 'audit'
        ];

        $this->em = self::prophesize(EntityManager::class);
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->security = self::prophesize(Security::class);
        $this->dateProvider = self::prophesize(DateTimeProvider::class);
    }

    /** @test */
    public function preUpdate_user_entity_email_updated_updated()
    {
        $changeSet = ['email' => ['p.bear@email.com', 'panda.bear@email.com' ]];
        $preUpdateEvent = new PreUpdateEventArgs($this->originalUser, $this->em->reveal(), $changeSet);

        $this->security->getUser()->willReturn($this->loggedInUser);
        $this->dateProvider->getDateTime()->willReturn($this->now);

        $sut = new UserListener($this->security->reveal(), $this->dateProvider->reveal(), $this->logger->reveal());
        $sut->preUpdate($this->originalUser, $preUpdateEvent);

        self::assertEquals($this->expectedLogEvent, $sut->logEvents[0], 'Expected the event in logEvents to match the expected event but it doesn\'t');
    }

    /** @test */
    public function postUpdate_user_entity_email_updated_updated()
    {
        $updatedUser = (clone $this->originalUser)->setEmail('panda.bear@email.com');

        $this->logger->notice('', $this->expectedLogEvent)->shouldBeCalled();

        $sut = new UserListener($this->security->reveal(), $this->dateProvider->reveal(), $this->logger->reveal());
        $postUpdateEvent = new LifecycleEventArgs($updatedUser, $this->em->reveal());

        $sut->logEvents[] = $this->expectedLogEvent;
        $sut->postUpdate($updatedUser, $postUpdateEvent);

        self::assertEquals([], $sut->logEvents);
    }
}
