<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RestClientException extends HttpException
{
    protected $data;

    public function __construct($message = 'API error occurred', $code = 404, array $data = [])
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
