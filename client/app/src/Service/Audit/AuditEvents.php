<?php

declare(strict_types=1);

namespace App\Service\Audit;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Model\Email;
use App\Service\Mailer\MailFactory;
use App\Service\Time\DateTimeProvider;

final class AuditEvents
{
    public const EVENT_USER_EMAIL_CHANGED = 'USER_EMAIL_CHANGED';
    public const EVENT_ROLE_CHANGED = 'ROLE_CHANGED';
    public const EVENT_CLIENT_EMAIL_CHANGED = 'CLIENT_EMAIL_CHANGED';
    public const EVENT_CLIENT_DELETED = 'CLIENT_DELETED';
    public const EVENT_DEPUTY_DELETED = 'DEPUTY_DELETED';
    public const EVENT_USER_SELF_REGISTER_FAILED = 'USER_SELF_REGISTER_FAILED';
    public const EVENT_USER_SELF_REGISTER_SUCCEEDED = 'USER_SELF_REGISTER_SUCCEEDED';
    public const EVENT_ADMIN_DELETED = 'ADMIN_DELETED';
    public const EVENT_REPORT_UNSUBMITTED = 'REPORT_UNSUBMITTED';
    public const EVENT_REPORT_RESUBMITTED = 'REPORT_RESUBMITTED';
    public const EVENT_USER_ADDED_TO_ORG = 'USER_ADDED_TO_ORG';
    public const EVENT_USER_REMOVED_FROM_ORG = 'USER_REMOVED_FROM_ORG';
    public const EVENT_CSV_UPLOADED = 'CSV_UPLOADED';
    public const EVENT_ORG_CREATED = 'ORG_CREATED';
    public const EVENT_ADMIN_MANAGER_CREATED = 'ADMIN_MANAGER_CREATED';
    public const EVENT_ADMIN_MANAGER_DELETED = 'ADMIN_MANAGER_DELETED';
    public const EVENT_EMAIL_NOT_SENT = 'EMAIL_NOT_SENT';
    public const EVENT_EMAIL_SENT = 'EMAIL_SENT';
    public const EVENT_DEPUTY_CHANGED_ORG = 'DEPUTY_CHANGED_ORG';
    public const EVENT_ADMIN_USER_REGISTER_SUCCEEDED = 'ADMIN_USER_REGISTER_SUCCEEDED';

    public const TRIGGER_ADMIN_USER_EDIT = 'ADMIN_USER_EDIT';
    public const TRIGGER_ADMIN_BUTTON = 'ADMIN_BUTTON';
    public const TRIGGER_ADMIN_MANAGER_MANUALLY_CREATED = 'ADMIN_MANAGER_MANUALLY_CREATED';
    public const TRIGGER_ADMIN_MANAGER_MANUALLY_DELETED = 'ADMIN_MANAGER_MANUALLY_DELETED';
    public const TRIGGER_CSV_UPLOAD = 'CSV_UPLOAD';
    public const TRIGGER_DEPUTY_USER_EDIT_CLIENT_DURING_REGISTRATION = 'TRIGGER_DEPUTY_USER_EDIT_CLIENT_DURING_REGISTRATION';
    public const TRIGGER_DEPUTY_USER_EDIT = 'DEPUTY_USER_EDIT';
    public const TRIGGER_DEPUTY_USER_EDIT_SELF = 'DEPUTY_USER_EDIT_SELF';
    public const TRIGGER_DEPUTY_USER_SELF_REGISTER_ATTEMPT = 'DEPUTY_USER_SELF_REGISTER_ATTEMPT';
    public const TRIGGER_DEPUTY_USER_REGISTRATION_FLOW_COMPLETED = 'DEPUTY_USER_REGISTRATION_FLOW_COMPLETED';
    public const TRIGGER_CODEPUTY_CREATED = 'CODEPUTY_CREATED';
    public const TRIGGER_ORG_USER_MANAGE_ORG_MEMBER = 'ORG_USER_MANAGE_ORG_MEMBER';
    public const TRIGGER_ADMIN_USER_MANAGE_ORG_MEMBER = 'ADMIN_USER_MANAGE_ORG_USER';
    public const TRIGGER_ADMIN_MANUAL_ORG_CREATION = 'ADMIN_MANUAL_ORG_CREATION';
    public const TRIGGER_UNSUBMIT_REPORT = 'UNSUBMIT_REPORT';
    public const TRIGGER_RESUBMIT_REPORT = 'RESUBMIT_REPORT';
    public const TRIGGER_DEPUTY_CHANGED_ORG = 'DEPUTY_CHANGED_ORG';
    public const TRIGGER_ADMIN_USER_REGISTER_ATTEMPT = 'ADMIN_USER_REGISTER_ATTEMPT';

    /**
     * @var DateTimeProvider
     */
    private $dateTimeProvider;

    public function __construct(DateTimeProvider $dateTimeProvider)
    {
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @throws \Exception
     */
    public function clientDischarged(
        string $trigger,
        string $caseNumber,
        string $dischargedBy,
        string $deputyName,
        ?\DateTime $deputyshipStartDate
    ): array {
        $event = [
            'trigger' => $trigger,
            'case_number' => $caseNumber,
            'discharged_by' => $dischargedBy,
            'deputy_name' => $deputyName,
            'discharged_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
            'deputyship_start_date' => $deputyshipStartDate ? $deputyshipStartDate->format(\DateTime::ATOM) : null,
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
            'changed_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
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
            'changed_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
            'changed_by' => $changedByEmail,
            'subject_full_name' => $subjectFullName,
            'subject_role' => 'CLIENT',
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_CLIENT_EMAIL_CHANGED);
    }

    /**
     * @throws \Exception
     */
    public function roleChanged(string $trigger, string $changedFrom, string $changedTo, string $changedByEmail, string $userChangedEmail): array
    {
        $event = [
            'trigger' => $trigger,
            'role_changed_from' => $changedFrom,
            'role_changed_to' => $changedTo,
            'changed_by' => $changedByEmail,
            'changed_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
            'user_changed' => $userChangedEmail,
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_ROLE_CHANGED);
    }

    /**
     * @return array|string[]
     *
     * @throws \Exception
     */
    public function userDeleted(string $trigger, string $deletedBy, string $subjectFullName, string $subjectEmail, string $subjectRole): array
    {
        $event = [
            'trigger' => $trigger,
            'deleted_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
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
     * @param string       $trigger      ,      what caused the event
     * @param User         $addedUser    ,    the user that was added to the org
     * @param Organisation $organisation , the org the user has been added to
     * @param User         $addedBy      ,      the user who added the the user to the org
     *
     * @return array|string[]
     *
     * @throws \Exception
     */
    public function userAddedToOrg(string $trigger, User $addedUser, Organisation $organisation, User $addedBy)
    {
        $event = [
            'trigger' => $trigger,
            'added_user_email' => $addedUser->getEmail(),
            'organisation_identifier' => $organisation->getEmailIdentifier(),
            'organisation_id' => $organisation->getId(),
            'added_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
            'added_by' => $addedBy->getEmail(),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_USER_ADDED_TO_ORG);
    }

    /**
     * @param string       $trigger      ,      what caused the event
     * @param User         $removedUser  ,  the user that was removed from the org
     * @param Organisation $organisation , the org the user has been removed from
     * @param User         $removedBy    ,    the user who removed the the user from the org
     *
     * @return array|string[]
     *
     * @throws \Exception
     */
    public function userRemovedFromOrg(string $trigger, User $removedUser, Organisation $organisation, User $removedBy)
    {
        $event = [
            'trigger' => $trigger,
            'removed_user_email' => $removedUser->getEmail(),
            'removed_user_name' => $removedUser->getFullName(),
            'organisation_identifier' => $organisation->getEmailIdentifier(),
            'organisation_id' => $organisation->getId(),
            'removed_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
            'removed_by' => $removedBy->getEmail(),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_USER_REMOVED_FROM_ORG);
    }

    /**
     * @param string $trigger             ,             what caused the event
     * @param User   $reportUnsubmittedBy ,
     *
     * @return array|string[]
     *
     * @throws \Exception
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
     * @throws \Exception
     */
    public function csvUploaded(
        string $trigger,
        string $roleType
    ): array {
        $event = [
            'trigger' => $trigger,
            'role_type' => $roleType,
            'changed_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_CSV_UPLOADED);
    }

    /**
     * @param string $trigger      , what caused the event
     * @param array  $organisation , the org the user has added
     * @param User   $currentUser  , return the logged in user
     *
     * @return array|string[]
     *
     * @throws \Exception
     */
    public function orgCreated(string $trigger, User $currentUser, array $organisation): array
    {
        $event = [
            'trigger' => $trigger,
            'created_by' => $currentUser->getEmail(),
            'organisation_id' => $organisation['id'],
            'organisation_name' => $organisation['name'],
            'organisation_identifier' => $organisation['email_identifier'],
            'organisation_status' => $organisation['is_activated'],
            'created_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_ORG_CREATED);
    }

    public function selfRegistrationFailed(array $failureData, string $errorMessage): array
    {
        $event = [
            'trigger' => AuditEvents::TRIGGER_DEPUTY_USER_SELF_REGISTER_ATTEMPT,
            'message' => $errorMessage,
        ] + $failureData;

        return $event + $this->baseEvent(AuditEvents::EVENT_USER_SELF_REGISTER_FAILED);
    }

    public function selfRegistrationSucceeded(User $registeredUser): array
    {
        $event = [
            'trigger' => AuditEvents::TRIGGER_DEPUTY_USER_SELF_REGISTER_ATTEMPT,
            'registered_user_email' => $registeredUser->getEmail(),
            'user_role' => $registeredUser->getRoleName(),
            'has_multi_deputy_order' => $registeredUser->getIsCoDeputy(),
            'created_by_case_manager' => $registeredUser->isCreatedByCaseManager(),
            'created_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_USER_SELF_REGISTER_SUCCEEDED);
    }

    public function adminRegistrationSucceeded(User $registeredUser): array
    {
        $event = [
            'trigger' => AuditEvents::TRIGGER_ADMIN_USER_REGISTER_ATTEMPT,
            'registered_user_email' => $registeredUser->getEmail(),
            'user_role' => $registeredUser->getRoleName(),
            'created_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_ADMIN_USER_REGISTER_SUCCEEDED);
    }

    /**
     * @param string $trigger             , what caused the event
     * @param User   $createdAdminManager , the newly created admin manager
     * @param User   $currentUser         , the logged in user
     *
     * @throws \Exception
     */
    public function adminManagerCreated(string $trigger, User $currentUser, User $createdAdminManager): array
    {
        $event = [
            'trigger' => $trigger,
            'logged_in_user_first_name' => $currentUser->getFirstname(),
            'logged_in_user_last_name' => $currentUser->getLastname(),
            'logged_in_user_email' => $currentUser->getEmail(),
            'admin_user_first_name' => $createdAdminManager->getFirstname(),
            'admin_user_last_name' => $createdAdminManager->getLastname(),
            'admin_user_email' => $createdAdminManager->getEmail(),
            'created_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_ADMIN_MANAGER_CREATED);
    }

    /**
     * @param string $trigger             , what caused the event
     * @param User   $deletedAdminManager , the deleted admin manager
     * @param User   $currentUser         , the logged in user
     *
     * @throws \Exception
     */
    public function adminManagerDeleted(string $trigger, User $currentUser, User $deletedAdminManager): array
    {
        $event = [
            'trigger' => $trigger,
            'logged_in_user_first_name' => $currentUser->getFirstname(),
            'logged_in_user_last_name' => $currentUser->getLastname(),
            'logged_in_user_email' => $currentUser->getEmail(),
            'admin_user_first_name' => $deletedAdminManager->getFirstname(),
            'admin_user_last_name' => $deletedAdminManager->getLastname(),
            'admin_user_email' => $deletedAdminManager->getEmail(),
            'created_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_ADMIN_MANAGER_DELETED);
    }

    public function emailSent(Email $email, User|string|null $loggedInUser): array
    {
        return $this->buildEmailEvent($email, $loggedInUser) + $this->baseEvent(AuditEvents::EVENT_EMAIL_SENT);
    }

    public function emailNotSent(Email $email, User|string|null $loggedInUser, \Throwable $error): array
    {
        return $this->buildEmailEvent($email, $loggedInUser) + $this->baseEvent(AuditEvents::EVENT_EMAIL_NOT_SENT) + ['error_message' => $error->getMessage()];
    }

    private function buildEmailEvent(Email $email, User|string|null $loggedInUser)
    {
        $class = new \ReflectionClass(MailFactory::class);
        $constants = array_flip($class->getConstants());

        $templateName = $constants[$email->getTemplate()];

        return [
            'logged_in_user_email' => ('anon.' == $loggedInUser) ? 'user not signed in' : $loggedInUser?->getEmail(),
            'recipient_email' => $email->getToEmail(),
            'template_name' => $templateName,
            'notify_template_id' => $email->getTemplate(),
            'email_parameters' => $email->getParameters(),
            'from_address_id' => $email->getFromEmailNotifyID(),
            'sent_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];
    }

    /**
     * @param string $trigger       , what caused the event
     * @param int    $deputyId      , deputyId
     * @param int    $previousOrgId , previous deputy organisation
     * @param int    $newOrgId      , new deputy organisation
     * @param int    $clientId      , the client that moved across with deputy
     */
    public function deputyChangedOrganisationEvent(string $trigger, int $deputyId, int $previousOrgId, int $newOrgId, int $clientId): array
    {
        $event = [
            'trigger' => $trigger,
            'date_deputy_changed' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
            'deputy_id' => $deputyId,
            'organisation_moved_from' => $previousOrgId,
            'organisation_moved_to' => $newOrgId,
            'clients_moved_over' => $clientId,
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_DEPUTY_CHANGED_ORG);
    }

    private function baseEvent(string $eventName): array
    {
        return [
            'event' => $eventName,
            'type' => 'audit',
        ];
    }
}
