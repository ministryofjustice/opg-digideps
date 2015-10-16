<?php
namespace AppBundle\Exception;

class UnauthorisedException extends \RuntimeException
{
    protected $code = 403;
    
}
