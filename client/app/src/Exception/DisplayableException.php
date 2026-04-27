<?php

namespace OPG\Digideps\Frontend\Exception;

class DisplayableException extends \RuntimeException
{
    public const int EXCEPTION_CODE = 900;

    protected $code = self::EXCEPTION_CODE;
}
