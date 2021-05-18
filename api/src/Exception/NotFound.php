<?php

namespace App\Exception;

class NotFound extends \RuntimeException
{
    public function __construct(string $message = 'No further details')
    {
        parent::__construct('Record not found. Details:'.$message, 404);
    }
}
