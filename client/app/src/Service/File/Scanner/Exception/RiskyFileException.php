<?php

namespace App\Service\File\Scanner\Exception;

class RiskyFileException extends \RuntimeException
{
    protected $message = 'Invalid PDF';
}
