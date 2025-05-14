<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

/**
 * Records the entities created and any errors when building from candidates for a single order.
 * This is the result of building entities for one court order, its associations to deputies and reports,
 * and updates to the order status and deputy status on order (if applicable).
 */
class DeputyshipBuilderResult
{
    public function __construct(
        // UID of the court order the built entities relate to
        private readonly ?string $uid = null,

        /** @var string[] $errors */
        private readonly array $errors = [],

        /** @var array<object> $entities */
        private readonly array $entities = [],
    ) {
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getCourtOrderUid(): ?string
    {
        return $this->uid;
    }

    /**
     * @return array<object>
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
