<?php

namespace App\Exception;

class UserWrongCredentials extends \RuntimeException
{
    protected $code = 498;
    protected $message = 'Cannot find user with the given credentials';
}
