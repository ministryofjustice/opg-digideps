<?php declare(strict_types=1);

namespace AppBundle\Service\Audit;

use AppBundle\Service\Time\DateTimeProvider;
use DateTime;

final class AuditEvents
{
    const EVENT_USER_EMAIL_CHANGED = 'USER_EMAIL_CHANGED';
    const EVENT_CLIENT_DISCHARGED = 'CLIENT_DISCHARGED';
    const EVENT_ROLE_CHANGED = 'ROLE_CHANGED';
    const EVENT_CLIENT_EMAIL_CHANGED = 'CLIENT_EMAIL_CHANGED';

    const TRIGGER_ADMIN_USER_EDIT = 'ADMIN_USER_EDIT';
    const TRIGGER_ADMIN_BUTTON = 'ADMIN_BUTTON';
    const TRIGGER_CSV_UPLOAD = 'CSV_UPLOAD';
    const TRIGGER_DEPUTY_USER = 'DEPUTY_USER';
    const TRIGGER_DEPUTY_USER_EDIT = 'DEPUTY_USER_EDIT';

    /**
     * @var DateTimeProvider
     */
    private $dateTimeProvider;

    public function __construct(DateTimeProvider $dateTimeProvider)
    {
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @param string $trigger
     * @param string $caseNumber
     * @param string $dischargedBy
     * @param string $deputyName
     * @param DateTime|null $deputyshipStartDate
     * @return array
     * @throws \Exception
     */
    public function clientDischarged(
        string $trigger,
        string $caseNumber,
        string $dischargedBy,
        string $deputyName,
        ?DateTime $deputyshipStartDate
    ): array
    {
        $event = [
            'trigger' => $trigger,
            'case_number' => $caseNumber,
            'discharged_by' => $dischargedBy,
            'deputy_name' => $deputyName,
            'discharged_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'deputyship_start_date' => $deputyshipStartDate ? $deputyshipStartDate->format(DateTime::ATOM) : null,
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_CLIENT_DISCHARGED);
    }

    public function userEmailChanged(
         string $trigger,
         string $emailChangedFrom,
         string $emailChangedTo,
         string $changedBy,
         string $subjectFullName,
         string $subjectRole
    )
    {
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
        string $changedBy,
        string $subjectFullName
    )
    {
        $event = [
            'trigger' => $trigger,
            'email_changed_from' => $emailChangedFrom,
            'email_changed_to' => $emailChangedTo,
            'changed_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'changed_by' => $changedBy,
            'subject_full_name' => $subjectFullName,
            'subject_role' => 'CLIENT',
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_CLIENT_EMAIL_CHANGED);
    }

    /**
     * @param string $eventName
     * @return array
     */
    private function baseEvent(string $eventName): array
    {
        return [
            'event' => $eventName,
            'type' => 'audit'
        ];
    }

    /**
     * @param string $trigger
     * @param string $changedFrom
     * @param string $changedTo
     * @param string $changedBy
     * @return array
     * @throws \Exception
     */
    public function roleChanged(string $trigger, string $changedFrom, string $changedTo, string $changedBy, string $userChanged): array
    {
        $event = [
            'trigger' => $trigger,
            'role_changed_from' => $changedFrom,
            'role_changed_to' => $changedTo,
            'changed_by' => $changedBy,
            'changed_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'user_changed' => $userChanged,
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_ROLE_CHANGED);
    }
}
