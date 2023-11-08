<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidRegistrationTokenException extends \RuntimeException
{
    protected $code = 498;
    protected $message = 'Registration token could not be matched to a User';
}
