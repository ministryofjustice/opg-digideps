<?php

namespace App\Exception;

class DisplayableException extends \RuntimeException
{
    const EXCEPTION_CODE = 900;

    protected $code = self::EXCEPTION_CODE;
}
