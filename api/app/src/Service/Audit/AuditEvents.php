<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Service\Audit;

use OPG\Digideps\Backend\Service\Time\DateTimeProvider;
use OPG\Digideps\Common\Mail\NotifyMail;
use Symfony\Component\Security\Core\User\UserInterface;

final class AuditEvents
{
    public const string EVENT_CLIENT_ARCHIVED = 'CLIENT_ARCHIVED';
    public const string TRIGGER_USER_ARCHIVED_CLIENT = 'USER_ARCHIVED_CLIENT';
    public const string USER_DELETED_AUTOMATION = 'USER_DELETED_AUTOMATION';
    public const string EVENT_EMAIL_NOT_SENT = 'EMAIL_NOT_SENT';
    public const string EVENT_EMAIL_SENT = 'EMAIL_SENT';

    public function __construct(private readonly DateTimeProvider $dateTimeProvider)
    {
    }

    public function clientArchived(
        string $trigger,
        string $caseNumber,
        ?\DateTime $deputyshipStartDate,
        string $archivedBy,
    ): array {
        $event = [
            'trigger' => $trigger,
            'case_number' => $caseNumber,
            'archived_by' => $archivedBy,
            'deputyship_start_date' => $deputyshipStartDate?->format(\DateTime::ATOM),
            'archived_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_CLIENT_ARCHIVED);
    }

    public function userAccountAutomatedDeletion(
        string $trigger,
        int $id,
        string $email,
    ): array {
        $event = [
            'trigger' => $trigger,
            'id' => $id,
            'email_address' => $email,
            'message' => 'Deletion due to two year retention policy',
            'deleted_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::USER_DELETED_AUTOMATION);
    }

    private function baseEvent(string $eventName): array
    {
        return [
            'event' => $eventName,
            'type' => 'audit',
        ];
    }

    public function emailSent(NotifyMail $email, ?UserInterface $loggedInUser): array
    {
        return $this->buildEmailEvent($email, $loggedInUser) + $this->baseEvent(self::EVENT_EMAIL_SENT);
    }

    public function emailNotSent(NotifyMail $email, ?UserInterface $loggedInUser, \Throwable $error): array
    {
        return $this->buildEmailEvent($email, $loggedInUser) + $this->baseEvent(self::EVENT_EMAIL_NOT_SENT) + ['error_message' => $error->getMessage()];
    }

    private function buildEmailEvent(NotifyMail $email, ?UserInterface $loggedInUser): array
    {
        $templateName = $email->template->name;

        return [
            'logged_in_user_email' => $loggedInUser?->getUserIdentifier() ?? 'user not signed in',
            'recipient_email' => $email->toEmail->value,
            'template_name' => $templateName,
            'notify_template_id' => $email->template->value,
            'email_parameters' => $email->parameters,
            'from_address_id' => $email->notifyFromId->toRfc4122(),
            'sent_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];
    }
}
