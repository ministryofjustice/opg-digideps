<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder;

final readonly class CourtOrderRelationshipResult
{
    private string $message;
    private string $errorMessage;

    public function __construct(
        CourtOrderRelationshipChange $change,
    ) {
        $this->message = "Changes in CourtOrder {$change->courtOrder->getId()}:"
            . ($change->hasSiblingIdChange() ? " SiblingId changed from '$change->oldSiblingId' -> '$change->newSiblingId'." : '')
            . ($change->hasKindChange() ? " Kind changed from '{$change->oldKind?->value}' -> '{$change->newKind->value}'." : '');

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
}
