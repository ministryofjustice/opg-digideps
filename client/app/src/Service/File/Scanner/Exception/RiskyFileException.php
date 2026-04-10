<?php

namespace OPG\Digideps\Frontend\Service\File\Scanner\Exception;

class RiskyFileException extends \RuntimeException
{
    protected $message = 'Invalid PDF';
}
