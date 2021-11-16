<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RestClientException extends HttpException
{
    public function __construct($message = 'API error occurred', $code = 404, protected array $data = [])
    {
        parent::__construct($code, $message);

        $this->code = $code;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
