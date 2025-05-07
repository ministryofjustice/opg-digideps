<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use Doctrine\ORM\Mapping\Entity;

class DeputyshipBuilderResult
{
    public function __construct(
        /** @var array<Entity> $entities */
        public readonly array $entities,
    ) {
    }
}
