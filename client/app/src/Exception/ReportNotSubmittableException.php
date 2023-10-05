<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ReportNotSubmittableException extends HttpException
{
    public function __construct(string $message = 'Report not ready for submission')
    {
        parent::__construct(404, $message);
    }
}
