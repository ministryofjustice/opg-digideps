<?php

declare(strict_types=1);

namespace App\Service\Audit;

use App\Entity\User;
use App\Model\Email;
use App\Service\Mailer\MailFactory;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\UserHelpers;
use DateTime;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AuditEventsTest extends TestCase
{
    use ProphecyTrait;

    private DateTime $now;
    private ObjectProphecy|DateTimeProvider $dateTimeProvider;

    public function setUp(): void
    {
        $this->now = new DateTime();
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($this->now);
    }

    /**
     * @test
     * @dataProvider startDateProvider
     */
    public function clientDischarged(?string $expectedStartDate, ?DateTime $actualStartDate): void
    {
        $expected = [
            'trigger' => 'ADMIN_BUTTON',
            'case_number' => '19348522',
            'discharged_by' => 'me@test.com',
            'deputy_name' => 'Bjork Gudmundsdottir',
            'discharged_on' => $this->now->format(DateTime::ATOM),
            'deputyship_start_date' => $expectedStartDate,
            'event' => 'CLIENT_DELETED',
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->clientDischarged(
            'ADMIN_BUTTON',
            '19348522',
            'me@test.com',
            'Bjork Gudmundsdottir',
            $actualStartDate
        );

        $this->assertEquals($expected, $actual);
    }

    public function startDateProvider()
    {
        return [
            'Start date present' => [
                '2019-07-08T09:36:00+01:00',
                new DateTime('2019-07-08T09:36', new DateTimeZone('+0100')),
            ],
            'Null start date' => [null, null],
        ];
    }

    /**
     * @test
     * @dataProvider emailChangeProvider
     */
    public function userEmailChanged()
    {
        $expected = [
            'trigger' => 'ADMIN_USER_EDIT',
            'email_changed_from' => 'me@test.com',
            'email_changed_to' => 'you@test.com',
            'changed_on' => $this->now->format(DateTime::ATOM),
            'changed_by' => 'super-admin@email.com',
            'subject_full_name' => 'Panda Bear',
            'subject_role' => 'ROLE_LAY_DEPUTY',
            'event' => 'USER_EMAIL_CHANGED',
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->userEmailChanged(
            'ADMIN_USER_EDIT',
            'me@test.com',
            'you@test.com',
            'super-admin@email.com',
            'Panda Bear',
            'ROLE_LAY_DEPUTY'
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider emailChangeProvider
     */
    public function clientEmailChanged(?string $oldEmail, ?string $newEmail)
    {
        $expected = [
            'trigger' => 'DEPUTY_USER_EDIT',
            'email_changed_from' => $oldEmail,
            'email_changed_to' => $newEmail,
            'changed_on' => $this->now->format(DateTime::ATOM),
            'changed_by' => 'super-admin@email.com',
            'subject_full_name' => 'Panda Bear',
            'subject_role' => 'CLIENT',
            'event' => 'CLIENT_EMAIL_CHANGED',
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->clientEmailChanged(
            'DEPUTY_USER_EDIT',
            $oldEmail,
            $newEmail,
            'super-admin@email.com',
            'Panda Bear'
        );

        $this->assertEquals($expected, $actual);
    }

    public function emailChangeProvider()
    {
        return [
            'Email changed' => ['me@test.com', 'you@test.com'],
            'Email removed' => ['me@test.com', null],
            'Email added' => [null, 'you@test.com'],
        ];
    }

    /**
     * @test
     * @dataProvider roleChangedProvider
     */
    public function roleChanged(string $trigger, $changedFrom, $changedTo, $changedBy, $userChanged): void
    {
        $expected = [
            'trigger' => $trigger,
            'role_changed_from' => $changedFrom,
            'role_changed_to' => $changedTo,
            'changed_by' => $changedBy,
            'user_changed' => $userChanged,
            'changed_on' => $this->now->format(DateTime::ATOM),
            'event' => AuditEvents::EVENT_ROLE_CHANGED,
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->roleChanged(
            $trigger,
            $changedFrom,
            $changedTo,
            $changedBy,
            $userChanged
        );

        $this->assertEquals($expected, $actual);
    }

    public function roleChangedProvider()
    {
        return [
            'PA to LAY' => ['ADMIN_BUTTON', 'ROLE_PA', 'ROLE_LAY_DEPUTY', 'polly.jean.harvey@test.com', 't.amos@test.com'],
            'PROF to PA' => ['ADMIN_BUTTON', 'ROLE_PROF', 'ROLE_PA', 't.amos@test.com', 'polly.jean.harvey@test.com'],
        ];
    }

    /**
     * @test
     */
    public function userDeletedDeputy(): void
    {
        $expected = [
            'trigger' => 'ADMIN_BUTTON',
            'deleted_on' => $this->now->format(DateTime::ATOM),
            'deleted_by' => 'super-admin@email.com',
            'subject_full_name' => 'Roisin Murphy',
            'subject_email' => 'r.murphy@email.com',
            'subject_role' => 'ROLE_LAY_DEPUTY',
            'event' => 'DEPUTY_DELETED',
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->userDeleted(
            'ADMIN_BUTTON',
            'super-admin@email.com',
            'Roisin Murphy',
            'r.murphy@email.com',
            'ROLE_LAY_DEPUTY'
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider adminRoleProvider
     */
    public function userDeletedAdmin(string $role): void
    {
        $expected = [
            'trigger' => 'ADMIN_BUTTON',
            'deleted_on' => $this->now->format(DateTime::ATOM),
            'deleted_by' => 'super-admin@email.com',
            'subject_full_name' => 'Robyn Konichiwa',
            'subject_email' => 'r.konichiwa@email.com',
            'subject_role' => $role,
            'event' => 'ADMIN_DELETED',
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->userDeleted(
            'ADMIN_BUTTON',
            'super-admin@email.com',
            'Robyn Konichiwa',
            'r.konichiwa@email.com',
            $role
        );

        $this->assertEquals($expected, $actual);
    }

    public function adminRoleProvider()
    {
        return [
            'admin' => [User::ROLE_ADMIN],
            'super admin' => [User::ROLE_SUPER_ADMIN],
        ];
    }

    /**
     * @test
     */
    public function orgCreated()
    {
        $currentUser = UserHelpers::createSuperAdminUser();
        $organisation =
            [
                'id' => 83,
                'name' => 'Your Organisation',
                'email_identifier' => 'mccracken.com',
                'is_activated' => 'TRUE',
            ];

        $expected = [
            'trigger' => 'ADMIN_MANUAL_ORG_CREATION',
            'created_by' => $currentUser->getEmail(),
            'organisation_id' => $organisation['id'],
            'organisation_name' => $organisation['name'],
            'organisation_identifier' => $organisation['email_identifier'],
            'organisation_status' => $organisation['is_activated'],
            'created_on' => $this->now->format(DateTime::ATOM),
            'event' => 'ORG_CREATED',
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->orgCreated(
            'ADMIN_MANUAL_ORG_CREATION',
            $currentUser,
            $organisation
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function adminManagerCreated()
    {
        $currentUser = UserHelpers::createSuperAdminUser();
        $createdAdminManager = UserHelpers::createAdminManager();

        $expected = [
            'trigger' => 'ADMIN_MANAGER_MANUALLY_CREATED',
            'logged_in_user_first_name' => $currentUser->getFirstname(),
            'logged_in_user_last_name' => $currentUser->getLastname(),
            'logged_in_user_email' => $currentUser->getEmail(),
            'admin_user_first_name' => $createdAdminManager->getFirstname(),
            'admin_user_last_name' => $createdAdminManager->getLastname(),
            'admin_user_email' => $createdAdminManager->getEmail(),
            'created_on' => $this->now->format(DateTime::ATOM),
            'event' => 'ADMIN_MANAGER_CREATED',
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->adminManagerCreated(
            'ADMIN_MANAGER_MANUALLY_CREATED',
            $currentUser,
            $createdAdminManager
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function adminManagerDeleted()
    {
        $currentUser = UserHelpers::createSuperAdminUser();
        $adminManagerToDelete = UserHelpers::createAdminManager();

        $expected = [
            'trigger' => 'ADMIN_MANAGER_MANUALLY_DELETED',
            'logged_in_user_first_name' => $currentUser->getFirstname(),
            'logged_in_user_last_name' => $currentUser->getLastname(),
            'logged_in_user_email' => $currentUser->getEmail(),
            'admin_user_first_name' => $adminManagerToDelete->getFirstname(),
            'admin_user_last_name' => $adminManagerToDelete->getLastname(),
            'admin_user_email' => $adminManagerToDelete->getEmail(),
            'created_on' => $this->now->format(DateTime::ATOM),
            'event' => 'ADMIN_MANAGER_DELETED',
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->adminManagerDeleted(
            'ADMIN_MANAGER_MANUALLY_DELETED',
            $currentUser,
            $adminManagerToDelete
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function emailSent()
    {
        $loggedInUser = UserHelpers::createSuperAdminUser();
        $email = (new Email())
            ->setTemplate(MailFactory::ACTIVATION_TEMPLATE_ID)
            ->setToEmail('a@b.com')
            ->setParameters(['some' => 'info'])
            ->setFromEmailNotifyID('abc123');

        $expected = [
            'logged_in_user_email' => $loggedInUser->getEmail(),
            'recipient_email' => 'a@b.com',
            'template_name' => 'ACTIVATION_TEMPLATE_ID',
            'notify_template_id' => '07e7fdb3-ad81-4105-b6b6-c3854e0c6caa',
            'email_parameters' => ['some' => 'info'],
            'from_address_id' => 'abc123',
            'sent_on' => $this->now->format(DateTime::ATOM),
            'event' => 'EMAIL_SENT',
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->emailSent(
            $email,
            $loggedInUser,
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function emailNotSent()
    {
        $loggedInUser = UserHelpers::createSuperAdminUser();
        $email = (new Email())
            ->setTemplate(MailFactory::ACTIVATION_TEMPLATE_ID)
            ->setToEmail('a@b.com')
            ->setParameters(['more' => 'stuff'])
            ->setFromEmailNotifyID('xyz987');

        $error = new Exception('Something went wrong');

        $expected = [
            'logged_in_user_email' => $loggedInUser->getEmail(),
            'recipient_email' => 'a@b.com',
            'template_name' => 'ACTIVATION_TEMPLATE_ID',
            'notify_template_id' => '07e7fdb3-ad81-4105-b6b6-c3854e0c6caa',
            'email_parameters' => ['more' => 'stuff'],
            'from_address_id' => 'xyz987',
            'sent_on' => $this->now->format(DateTime::ATOM),
            'event' => 'EMAIL_NOT_SENT',
            'type' => 'audit',
            'error_message' => 'Something went wrong',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->emailNotSent(
            $email,
            $loggedInUser,
            $error
        );

        $this->assertEquals($expected, $actual);
    }
}
