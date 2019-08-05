<?php

namespace AppBundle\Exception;

class UserWrongCredentialsManyAttempts extends \RuntimeException
{
    protected $code = 499;
}
