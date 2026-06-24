<?php

namespace OPG\Digideps\Backend\Exception;

class UserWrongCredentialsManyAttempts extends \RuntimeException
{
    protected $code = 499;
}
