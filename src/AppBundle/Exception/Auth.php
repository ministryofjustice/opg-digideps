<?php
namespace AppBundle\Exception;

class Auth extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct('Record not found. Details:' . $message, 403);
    }
    
}
