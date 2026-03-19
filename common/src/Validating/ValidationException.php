<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Validating;

class ValidationException extends \Exception
{
    public function __construct(string $expected, mixed $got)
    {
        parent::__construct("Expected value of type {$expected}. Got: " . var_export($got, true));
    }
}
