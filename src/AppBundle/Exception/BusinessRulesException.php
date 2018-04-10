<?php

namespace AppBundle\Exception;

/**
 * Class BusinessRulesException
 * Used for Exceptions thrown by API layer that contain data useful to the error message displayed to the user.
 *
 * @package AppBundle\Exception
 */
class BusinessRulesException extends \RuntimeException implements HasDataInterface
{
    protected $code = 401;

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
