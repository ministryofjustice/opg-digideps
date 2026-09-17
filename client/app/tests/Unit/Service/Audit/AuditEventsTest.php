<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Audit;

use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Model\Email;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Mailer\MailFactory;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuditEventsTest extends TestCase
{
    private \DateTime $now;
    private DateTimeProvider&MockObject $dateTimeProvider;

    public function setUp(): void
    {
        $this->now = new \DateTime();
        $this->dateTimeProvider = self::createMock(DateTimeProvider::class);
        $this->dateTimeProvider->expects(self::once())->method('getDateTime')->willReturn($this->now);
    }

    /**
     * @dataProvider startDateProvider
     */
    public function testClientDischarged(?string $expectedStartDate, ?\DateTime $actualStartDate): void
    {
        $expected = [
            'trigger' => 'ADMIN_BUTTON',
            'case_number' => '19348522',
            'discharged_by' => 'me@test.com',
            'deputy_name' => 'Bjork Gudmundsdottir',
            'discharged_on' => $this->now->format(\DateTime::ATOM),
            'deputyship_start_date' => $expectedStartDate,
            'event' => 'CLIENT_DELETED',
            'type' => 'audit',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->clientDischarged(
            'ADMIN_BUTTON',
            '19348522',
            'me@test.com',
            'Bjork Gudmundsdottir',
            $actualStartDate
        );

        self::assertEquals($expected, $actual);
    }

    public static function startDateProvider(): array
    {
        return [
            'Start date present' => [
                '2019-07-08T09:36:00+01:00',
                new \DateTime('2019-07-08T09:36', new \DateTimeZone('+0100')),
            ],
            'Null start date' => [null, null],
        ];
    }

    /**
     * @dataProvider emailChangeProvider
     */
    public function testUserEmailChanged(): void
    {
        $expected = [
            'trigger' => 'ADMIN_USER_EDIT',
            'email_changed_from' => 'me@test.com',
            'email_changed_to' => 'you@test.com',
            'changed_on' => $this->now->format(\DateTime::ATOM),
            'changed_by' => 'super-admin@email.com',
            'subject_full_name' => 'Panda Bear',
            'subject_role' => 'ROLE_LAY_DEPUTY',
            'event' => 'USER_EMAIL_CHANGED',
            'type' => 'audit',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->userEmailChanged(
            'ADMIN_USER_EDIT',
            'me@test.com',
            'you@test.com',
            'super-admin@email.com',
            'Panda Bear',
            'ROLE_LAY_DEPUTY'
        );

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider emailChangeProvider
     */
    public function testClientEmailChanged(?string $oldEmail, ?string $newEmail): void
    {
        $expected = [
            'trigger' => 'DEPUTY_USER_EDIT',
            'email_changed_from' => $oldEmail,
            'email_changed_to' => $newEmail,
            'changed_on' => $this->now->format(\DateTime::ATOM),
            'changed_by' => 'super-admin@email.com',
            'subject_full_name' => 'Panda Bear',
            'subject_role' => 'CLIENT',
            'event' => 'CLIENT_EMAIL_CHANGED',
            'type' => 'audit',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->clientEmailChanged(
            'DEPUTY_USER_EDIT',
            $oldEmail,
            $newEmail,
            'super-admin@email.com',
            'Panda Bear'
        );

        self::assertEquals($expected, $actual);
    }

    public static function emailChangeProvider(): array
    {
        return [
            'Email changed' => ['me@test.com', 'you@test.com'],
            'Email removed' => ['me@test.com', null],
            'Email added' => [null, 'you@test.com'],
        ];
    }

    /**
     * @dataProvider roleChangedProvider
     */
    public function testRoleChanged(
        string $trigger,
        string $changedFrom,
        string $changedTo,
        string $changedBy,
        string $userChanged
    ): void {
        $expected = [
            'trigger' => $trigger,
            'role_changed_from' => $changedFrom,
            'role_changed_to' => $changedTo,
            'changed_by' => $changedBy,
            'user_changed' => $userChanged,
            'changed_on' => $this->now->format(\DateTime::ATOM),
            'event' => AuditEvents::EVENT_ROLE_CHANGED,
            'type' => 'audit',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->roleChanged(
            $trigger,
            $changedFrom,
            $changedTo,
            $changedBy,
            $userChanged
        );

        self::assertEquals($expected, $actual);
    }

    public static function roleChangedProvider(): array
    {
        return [
            'PA to LAY' => ['ADMIN_BUTTON', 'ROLE_PA', 'ROLE_LAY_DEPUTY', 'polly.jean.harvey@test.com', 't.amos@test.com'],
            'PROF to PA' => ['ADMIN_BUTTON', 'ROLE_PROF', 'ROLE_PA', 't.amos@test.com', 'polly.jean.harvey@test.com'],
        ];
    }

    public function testUserDeletedDeputy(): void
    {
        $expected = [
            'trigger' => 'ADMIN_BUTTON',
            'deleted_on' => $this->now->format(\DateTime::ATOM),
            'deleted_by' => 'super-admin@email.com',
            'subject_full_name' => 'Roisin Murphy',
            'subject_email' => 'r.murphy@email.com',
            'subject_role' => 'ROLE_LAY_DEPUTY',
            'event' => 'DEPUTY_DELETED',
            'type' => 'audit',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->userDeleted(
            'ADMIN_BUTTON',
            'super-admin@email.com',
            'Roisin Murphy',
            'r.murphy@email.com',
            'ROLE_LAY_DEPUTY'
        );

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider adminRoleProvider
     */
    public function testUserDeletedAdmin(string $role): void
    {
        $expected = [
            'trigger' => 'ADMIN_BUTTON',
            'deleted_on' => $this->now->format(\DateTime::ATOM),
            'deleted_by' => 'super-admin@email.com',
            'subject_full_name' => 'Robyn Konichiwa',
            'subject_email' => 'r.konichiwa@email.com',
            'subject_role' => $role,
            'event' => 'ADMIN_DELETED',
            'type' => 'audit',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->userDeleted(
            'ADMIN_BUTTON',
            'super-admin@email.com',
            'Robyn Konichiwa',
            'r.konichiwa@email.com',
            $role
        );

        self::assertEquals($expected, $actual);
    }

    public static function adminRoleProvider(): array
    {
        return [
            'admin' => [User::ROLE_ADMIN],
            'super admin' => [User::ROLE_SUPER_ADMIN],
        ];
    }

    public function testOrgCreated(): void
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
            'created_on' => $this->now->format(\DateTime::ATOM),
            'event' => 'ORG_CREATED',
            'type' => 'audit',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->orgCreated(
            'ADMIN_MANUAL_ORG_CREATION',
            $currentUser,
            $organisation
        );

        self::assertEquals($expected, $actual);
    }

    public function testAdminManagerCreated(): void
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
            'created_on' => $this->now->format(\DateTime::ATOM),
            'event' => 'ADMIN_MANAGER_CREATED',
            'type' => 'audit',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->adminManagerCreated(
            'ADMIN_MANAGER_MANUALLY_CREATED',
            $currentUser,
            $createdAdminManager
        );

        self::assertEquals($expected, $actual);
    }

    public function testAdminManagerDeleted(): void
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
            'created_on' => $this->now->format(\DateTime::ATOM),
            'event' => 'ADMIN_MANAGER_DELETED',
            'type' => 'audit',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->adminManagerDeleted(
            'ADMIN_MANAGER_MANUALLY_DELETED',
            $currentUser,
            $adminManagerToDelete
        );

        self::assertEquals($expected, $actual);
    }

    public function testEmailSent(): void
    {
        $loggedInUser = UserHelpers::createSuperAdminUser();
        $email = new Email()
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
            'sent_on' => $this->now->format(\DateTime::ATOM),
            'event' => 'EMAIL_SENT',
            'type' => 'audit',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->emailSent(
            $email,
            $loggedInUser,
        );

        self::assertEquals($expected, $actual);
    }

    public function testEmailNotSent(): void
    {
        $loggedInUser = UserHelpers::createSuperAdminUser();
        $email = new Email()
            ->setTemplate(MailFactory::ACTIVATION_TEMPLATE_ID)
            ->setToEmail('a@b.com')
            ->setParameters(['more' => 'stuff'])
            ->setFromEmailNotifyID('xyz987');

        $error = new \Exception('Something went wrong');

        $expected = [
            'logged_in_user_email' => $loggedInUser->getEmail(),
            'recipient_email' => 'a@b.com',
            'template_name' => 'ACTIVATION_TEMPLATE_ID',
            'notify_template_id' => '07e7fdb3-ad81-4105-b6b6-c3854e0c6caa',
            'email_parameters' => ['more' => 'stuff'],
            'from_address_id' => 'xyz987',
            'sent_on' => $this->now->format(\DateTime::ATOM),
            'event' => 'EMAIL_NOT_SENT',
            'type' => 'audit',
            'error_message' => 'Something went wrong',
        ];

        $actual = new AuditEvents($this->dateTimeProvider)->emailNotSent(
            $email,
            $loggedInUser,
            $error
        );

        self::assertEquals($expected, $actual);
    }
}
