<?php

namespace OPG\Digideps\Frontend\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedSessionException extends HttpException
{
    public function __construct(string $message = 'The users session could not be authenticated')
    {
        parent::__construct(403, $message);
    }
}
