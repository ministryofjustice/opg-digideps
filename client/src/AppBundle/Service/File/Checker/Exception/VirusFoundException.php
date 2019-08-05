<?php

namespace AppBundle\Service\File\Checker\Exception;

class VirusFoundException extends \RuntimeException
{
    protected $message = 'Found virus in file';
}
