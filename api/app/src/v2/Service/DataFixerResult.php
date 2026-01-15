<?php

declare(strict_types=1);

namespace App\v2\Service;

class DataFixerResult
{
    public function __construct(
        public bool $success = true,
    ) {
    }
}
