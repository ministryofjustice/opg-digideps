<?php

declare(strict_types=1);

namespace App\Service\Audit;

use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Service\Time\DateTimeProvider;
use DateTime;
use Exception;

final class AuditEvents
{
    const EVENT_USER_EMAIL_CHANGED = 'USER_EMAIL_CHANGED';
    const EVENT_ROLE_CHANGED = 'ROLE_CHANGED';
    const EVENT_CLIENT_EMAIL_CHANGED = 'CLIENT_EMAIL_CHANGED';
    const EVENT_CLIENT_DELETED = 'CLIENT_DELETED';
    const EVENT_DEPUTY_DELETED = 'DEPUTY_DELETED';
    const EVENT_USER_SELF_REGISTER_FAILED = 'USER_SELF_REGISTER_FAILED';
    const EVENT_ADMIN_DELETED = 'ADMIN_DELETED';
    const EVENT_REPORT_UNSUBMITTED = 'REPORT_UNSUBMITTED';
    const EVENT_REPORT_RESUBMITTED = 'REPORT_RESUBMITTED';
    const EVENT_USER_ADDED_TO_ORG = 'USER_ADDED_TO_ORG';
    const EVENT_USER_REMOVED_FROM_ORG = 'USER_REMOVED_FROM_ORG';
    const EVENT_CSV_UPLOADED = 'CSV_UPLOADED';

    const TRIGGER_ADMIN_USER_EDIT = 'ADMIN_USER_EDIT';
    const TRIGGER_ADMIN_BUTTON = 'ADMIN_BUTTON';
    const TRIGGER_CSV_UPLOAD = 'CSV_UPLOAD';
    const TRIGGER_DEPUTY_USER_ADD_CLIENT_IN_REGISTRATION = 'DEPUTY_USER_ADD_CLIENT_IN_REGISTRATION';
    const TRIGGER_DEPUTY_USER_EDIT_SELF = 'DEPUTY_USER_EDIT_SELF';
    const TRIGGER_DEPUTY_USER_EDIT = 'DEPUTY_USER_EDIT';
    const TRIGGER_DEPUTY_USER_SELF_REGISTER_ATTEMPT = 'DEPUTY_USER_SELF_REGISTER_ATTEMPT';
    const TRIGGER_CODEPUTY_CREATED = 'CODEPUTY_CREATED';
    const TRIGGER_ORG_USER_MANAGE_ORG_MEMBER = 'ORG_USER_MANAGE_ORG_MEMBER';
    const TRIGGER_ADMIN_USER_MANAGE_ORG_MEMBER = 'ADMIN_USER_MANAGE_ORG_USER';
    const TRIGGER_UNSUBMIT_REPORT = 'UNSUBMIT_REPORT';
    const TRIGGER_RESUBMIT_REPORT = 'RESUBMIT_REPORT';

    /**
     * @var DateTimeProvider
     */
    private $dateTimeProvider;

    public function __construct(DateTimeProvider $dateTimeProvider)
    {
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @throws Exception
     */
    public function clientDischarged(
        string $trigger,
        string $caseNumber,
        string $dischargedBy,
        string $deputyName,
        ?DateTime $deputyshipStartDate
    ): array {
        $event = [
            'trigger' => $trigger,
            'case_number' => $caseNumber,
            'discharged_by' => $dischargedBy,
            'deputy_name' => $deputyName,
            'discharged_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'deputyship_start_date' => $deputyshipStartDate ? $deputyshipStartDate->format(DateTime::ATOM) : null,
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_CLIENT_DELETED);
    }

    public function userEmailChanged(
        string $trigger,
        string $emailChangedFrom,
        string $emailChangedTo,
        string $changedBy,
        string $subjectFullName,
        string $subjectRole
    ) {
        $event = [
            'trigger' => $trigger,
            'email_changed_from' => $emailChangedFrom,
            'email_changed_to' => $emailChangedTo,
            'changed_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'changed_by' => $changedBy,
            'subject_full_name' => $subjectFullName,
            'subject_role' => $subjectRole,
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_USER_EMAIL_CHANGED);
    }

    public function clientEmailChanged(
        string $trigger,
        ?string $emailChangedFrom,
        ?string $emailChangedTo,
        string $changedByEmail,
        string $subjectFullName
    ) {
        $event = [
            'trigger' => $trigger,
            'email_changed_from' => $emailChangedFrom,
            'email_changed_to' => $emailChangedTo,
            'changed_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'changed_by' => $changedByEmail,
            'subject_full_name' => $subjectFullName,
            'subject_role' => 'CLIENT',
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_CLIENT_EMAIL_CHANGED);
    }

    /**
     * @throws Exception
     */
    public function roleChanged(string $trigger, string $changedFrom, string $changedTo, string $changedByEmail, string $userChangedEmail): array
    {
        $event = [
            'trigger' => $trigger,
            'role_changed_from' => $changedFrom,
            'role_changed_to' => $changedTo,
            'changed_by' => $changedByEmail,
            'changed_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'user_changed' => $userChangedEmail,
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_ROLE_CHANGED);
    }

    /**
     * @return array|string[]
     *
     * @throws Exception
     */
    public function userDeleted(string $trigger, string $deletedBy, string $subjectFullName, string $subjectEmail, string $subjectRole): array
    {
        $event = [
            'trigger' => $trigger,
            'deleted_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'deleted_by' => $deletedBy,
            'subject_full_name' => $subjectFullName,
            'subject_email' => $subjectEmail,
            'subject_role' => $subjectRole,
        ];

        $eventType = in_array($subjectRole, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]) ?
            AuditEvents::EVENT_ADMIN_DELETED : AuditEvents::EVENT_DEPUTY_DELETED;

        return $event + $this->baseEvent($eventType);
    }

    /**
     * @param string       $trigger,      what caused the event
     * @param User         $addedUser,    the user that was added to the org
     * @param Organisation $organisation, the org the user has been added to
     * @param User         $addedBy,      the user who added the the user to the org
     *
     * @return array|string[]
     *
     * @throws Exception
     */
    public function userAddedToOrg(string $trigger, User $addedUser, Organisation $organisation, User $addedBy)
    {
        $event = [
            'trigger' => $trigger,
            'added_user_email' => $addedUser->getEmail(),
            'organisation_identifier' => $organisation->getEmailIdentifier(),
            'organisation_id' => $organisation->getId(),
            'added_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'added_by' => $addedBy->getEmail(),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_USER_ADDED_TO_ORG);
    }

    /**
     * @param string       $trigger,      what caused the event
     * @param User         $removedUser,  the user that was removed from the org
     * @param Organisation $organisation, the org the user has been removed from
     * @param User         $removedBy,    the user who removed the the user from the org
     *
     * @return array|string[]
     *
     * @throws Exception
     */
    public function userRemovedFromOrg(string $trigger, User $removedUser, Organisation $organisation, User $removedBy)
    {
        $event = [
            'trigger' => $trigger,
            'removed_user_email' => $removedUser->getEmail(),
            'removed_user_name' => $removedUser->getFullName(),
            'organisation_identifier' => $organisation->getEmailIdentifier(),
            'organisation_id' => $organisation->getId(),
            'removed_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'removed_by' => $removedBy->getEmail(),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_USER_REMOVED_FROM_ORG);
    }

    /**
     * @param string $trigger,             what caused the event
     * @param User   $reportUnsubmittedBy,
     *
     * @return array|string[]
     *
     * @throws Exception
     */
    public function reportUnsubmitted(Report $unsubmittedReport, User $reportUnsubmittedBy, string $trigger)
    {
        $event = [
            'trigger' => $trigger,
            'deputy_user' => $reportUnsubmittedBy->getId(),
            'report_id' => $unsubmittedReport->getId(),
            'date_unsubmitted' => $unsubmittedReport->getUnSubmitDate(),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_REPORT_UNSUBMITTED);
    }

    /**
     * @return array|string[]
     */
    public function reportResubmitted(Report $resubmittedReport, User $reportSubmittedBy)
    {
        $event = [
            'trigger' => AuditEvents::TRIGGER_RESUBMIT_REPORT,
            'deputy_user' => $reportSubmittedBy->getId(),
            'report_id' => $resubmittedReport->getId(),
            'date_resubmitted' => $resubmittedReport->getSubmitDate(),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_REPORT_RESUBMITTED);
    }

    /**
     * @throws Exception
     */
    public function csvUploaded(
        string $trigger,
        string $roleType
    ): array {
        $event = [
            'trigger' => $trigger,
            'role_type' => $roleType,
            'changed_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_CSV_UPLOADED);
    }

    public function selfRegistrationFailed(array $failureData): array
    {
        $event = [
            'trigger' => AuditEvents::TRIGGER_DEPUTY_USER_SELF_REGISTER_ATTEMPT,
        ] + $failureData;

        return $event + $this->baseEvent(AuditEvents::EVENT_USER_SELF_REGISTER_FAILED);
    }

    private function baseEvent(string $eventName): array
    {
        return [
            'event' => $eventName,
            'type' => 'audit',
        ];
    }
}
