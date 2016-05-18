<?php

namespace AppBundle\Exception;

class RestClientException extends DisplayableException
{
    protected $data;

    public function __construct($message, $code, array $data = [])
    {
        parent::__construct($message, $code);

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
