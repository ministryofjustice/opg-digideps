<?php

namespace AppBundle\Service\File\Scanner\Exception;

class VirusFoundException extends \RuntimeException
{
    protected $message = 'Found virus in file';
}
