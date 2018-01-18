<?php

namespace AppBundle\Entity;

/**
 * Common functionalities among Report and NDR
 */
abstract class AbstractReport
{
    abstract function getClient();
    abstract function createAttachmentName($format);

}
