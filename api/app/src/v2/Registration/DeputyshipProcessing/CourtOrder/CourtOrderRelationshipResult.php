<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder;

final class CourtOrderRelationshipResult
{
    private string $message;
    private string $errorMessage;

    public function __construct(CourtOrderRelationshipChange $change)
    {
        $this->message = "Changes in CourtOrder {$change->courtOrderId}:"
            . ($change->hasSiblingIdChange() ? " SiblingId changed from '$change->oldSiblingId' -> '$change->currentSiblingId'." : '')
            . ($change->hasKindChange() ? " Kind changed from '{$change->oldKind?->value}' -> '{$change->currentKind->value}'." : '');

        $this->errorMessage = '';
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function isError(): bool
    {
        return $this->errorMessage !== '';
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function appendError(string $error): CourtOrderRelationshipResult
    {
        $this->errorMessage .= '; ' . $error;
        return $this;
    }

    public function appendMessage(string $message): CourtOrderRelationshipResult
    {
        $this->message .= '; ' . $message;
        return $this;
    }
}
