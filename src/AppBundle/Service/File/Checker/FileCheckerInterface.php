<?php

namespace AppBundle\Service\File\Checker;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileCheckerInterface
{
    public function checkFile(UploadedFile $file);
}
