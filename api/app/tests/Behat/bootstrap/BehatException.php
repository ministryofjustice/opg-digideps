<?php

namespace App\Tests\Behat;

use Exception;
use Throwable;

class BehatException extends Exception
{
    public function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
