<?php

namespace App\Exception;

class UserWrongCredentialsManyAttempts extends \RuntimeException
{
    protected $code = 499;
}
