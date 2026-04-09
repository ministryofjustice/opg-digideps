<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\CourtOrder;

final readonly class CourtOrderRelationshipResult
{
    private string $message;
    private string $errorMessage;

    public function __construct(private CourtOrderRelationshipChange $change)
    {
        $this->message = "Changes in CourtOrder {$this->change->courtOrder->getId()}:"
            . ($this->change->hasSiblingIdChange() ? " SiblingId changed from '{$this->change->oldSiblingId}' -> '{$this->change->courtOrder->getSibling()?->getId()}'." : '')
            . ($this->change->hasKindChange() ? " Kind changed from '{$this->change->oldKind?->value}' -> '{$this->change->courtOrder->getOrderKind()->value}'." : '');
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
