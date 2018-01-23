<?php

namespace AppBundle\Entity;

/**
 * Common functionalities among Report and NDR
 */
abstract class AbstractReport
{
    abstract function getId();
    abstract function getType();
    abstract function getClient();
    abstract function createAttachmentName($format);
    abstract function getZipName();
}
