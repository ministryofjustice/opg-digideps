<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use Doctrine\ORM\Mapping\Entity;

class DeputyshipBuilderResult
{
    public function __construct(
        /** @var string[] $errors */
        private readonly array $errors = [],

        /** @var Entity[] $entities */
        private readonly array $entities = [],
    ) {
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * @return Entity[]
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
