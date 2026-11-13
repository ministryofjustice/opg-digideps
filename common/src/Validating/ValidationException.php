<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Validating;

class ValidationException extends \Exception
{
    public function __construct(null|int|string $key, string $expected, mixed $got)
    {
        $key ??= 'NULL';
        parent::__construct("Expected value of type {$expected} for key {$key}. Got: " . var_export($got, true));
    }
}
