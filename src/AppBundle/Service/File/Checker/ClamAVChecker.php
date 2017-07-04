<?php

namespace AppBundle\Service\File\Checker;


use AppBundle\Service\File\Checker\Exception\VirusFoundException;

class ClamAVChecker implements FileCheckerInterface
{
    public function checkFile($body)
    {
        throw new VirusFoundException("Found virus in file");
    }

}