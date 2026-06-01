<?php

namespace OPG\Digideps\Backend\Exception;

class UnauthorisedException extends \RuntimeException implements HasDataInterface
{
    protected $code = 403;

    protected mixed $data = null;

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }
}
