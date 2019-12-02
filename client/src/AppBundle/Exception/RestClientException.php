<?php

namespace AppBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RestClientException extends HttpException
{
    protected $data;

    public function __construct($message, $code, array $data = [])
    {
        parent::__construct($code, $message);

        $this->code = $code;
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
