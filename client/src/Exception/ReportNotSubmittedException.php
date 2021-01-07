<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ReportNotSubmittedException extends HttpException
{
    public function __construct(string $message = 'Report not submitted')
    {
        parent::__construct(404, $message);
    }
}
