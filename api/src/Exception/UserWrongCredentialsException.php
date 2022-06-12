<?php

declare(strict_types=1);

namespace App\Exception;

class UserWrongCredentialsException extends \RuntimeException
{
    protected $code = 498;
    protected $message = 'Cannot find user with the given credentials';
}
