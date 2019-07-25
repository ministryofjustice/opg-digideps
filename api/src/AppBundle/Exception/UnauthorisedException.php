<?php

namespace AppBundle\Exception;

class UnauthorisedException extends \RuntimeException implements HasDataInterface
{
    protected $code = 403;

    protected $data;

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
