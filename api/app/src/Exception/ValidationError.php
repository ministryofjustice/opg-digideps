<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Exception;

class ValidationError extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 422);
    }
}
