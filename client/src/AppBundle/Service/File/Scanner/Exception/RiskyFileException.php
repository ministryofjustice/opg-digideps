<?php

namespace AppBundle\Service\File\Scanner\Exception;

class RiskyFileException extends \RuntimeException
{
    protected $message = 'Invalid PDF';
}
