<?php

declare(strict_types=1);

namespace App\Factory;

class DataFactoryResult
{
    public function __construct(
        public bool $success = true,
    ) {
    }
}
