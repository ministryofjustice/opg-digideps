<?php

namespace AppBundle\Exception;

class NotFound extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct('Record not found. Details:'.$message, 404);
    }
}
