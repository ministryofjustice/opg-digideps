<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UserWrongCredentialsException extends HttpException
{
    public function __construct(string $message = 'The users username and/or password were incorrect', int $statusCode = 403)
    {
        parent::__construct($statusCode, $message);
    }
}
