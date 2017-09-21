<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;

class Jpg extends UploadableFile
{
    protected $scannerEndpoint = 'upload/jpeg';

}

