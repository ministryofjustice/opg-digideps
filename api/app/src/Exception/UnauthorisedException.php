<?php

namespace OPG\Digideps\Backend\Exception;

final class UnauthorisedException extends \RuntimeException
{
    public function __construct(string $message = "", int $code = 403, ?\Throwable $previous = null, public ?float $data = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
