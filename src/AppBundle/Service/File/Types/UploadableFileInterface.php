<?php

namespace AppBundle\Service\File\Types;

interface UploadableFileInterface
{
    public function checkFile();

    public function getFileCheckers();
}
