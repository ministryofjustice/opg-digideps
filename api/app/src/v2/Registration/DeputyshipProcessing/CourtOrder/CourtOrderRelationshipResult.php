<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\CourtOrder;

final readonly class CourtOrderRelationshipResult
{
    public function __construct(private CourtOrderRelationshipChange $change)
    {
    }

    public function getMessage(): string
    {
        return "Changes in CourtOrder {$this->change->courtOrder->getId()}:"
            . ($this->change->hasSiblingIdChange() ? " SiblingId changed from '{$this->change->oldSiblingId}' -> '{$this->change->courtOrder->getSibling()?->getId()}'." : '')
            . ($this->change->hasKindChange() ? "Kind changed from '{$this->change->oldKind?->value}' -> '{$this->change->courtOrder->getKind()?->value}'" : '');
    }

    public function isError(): bool
    {
        return false;
    }

    public function getErrorMessage(): string
    {
        return "";
    }
}
