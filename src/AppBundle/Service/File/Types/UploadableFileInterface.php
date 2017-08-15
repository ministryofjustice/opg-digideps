<?php

namespace AppBundle\Service\File\Types;

interface UploadableFileInterface
{
    public function callFileCheckers();

    public function getFileCheckers();
}
