<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ReportSubmittedException extends HttpException
{
    public function __construct(string $message = 'Report already submitted and not editable')
    {
        parent::__construct(404, $message);
    }
}
